<?php
declare(strict_types=1);

namespace App\Console;

use App\Db;
use App\User;
use App\Wamp\Authorization;
use App\Wamp\KeepAliveClient;
use Bunny\Channel;
use Bunny\Message;
use Dvelum\Config;
use App\Wamp\AuthProviderClient;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Peer\Client;
use Thruway\Peer\Router;
use Thruway\Realm;
use Thruway\Transport\InternalClientTransportProvider;
use Thruway\Transport\RatchetTransportProvider;
use Bunny\Async;

class Wsd extends DbWorker{
    private LoopInterface $loop;
    private $router;
    private $address;
    private $port;
    private $realm;
    private $cfg;
    private Client $wsPublisher;

    /**
     * @var Channel
     */
    protected $rabbitChannel;
    protected $rabbitConnection;

    protected function action(): bool {
        ini_set('session.auto_start', '0');
        ini_set('session.cache_limiter', '');
        ini_set('session.use_cookies', '0');

        $this->cfg = Config::storage()->get('ccng.php');
        $this->address = $this->cfg->get('ws')['address'];
        $this->port = $this->cfg->get('ws')['port'];
        $wsRealm = $this->cfg->get('ws')['realm'];

        $workerCfgFile = $_SERVER['argv'][2];
        if(!file_exists($workerCfgFile))
            throw new \Exception('Wrong worker cfg file: '.$workerCfgFile);

        $workerCfg = require $workerCfgFile;

        $this->db = Db::factory('main');
        User::factory(1)->setAuthKey($workerCfg['authkey'])->dbSetAuth($this->db);

        $this->loop = Loop::get();
        $this->router = new Router($this->loop);
        $this->router->getRealmManager()->getRealm('thruway.auth');

        $keepAliveClient = new KeepAliveClient($wsRealm);
        $keepAliveClient->setRealm($wsRealm);
        $internalTransportProvider = new InternalClientTransportProvider($keepAliveClient);
        $authMgr = new AuthenticationManager();
        $transportProvider = new RatchetTransportProvider($this->address, $this->port);

        $authorizeManager = (new Authorization($wsRealm,$this->loop))
            ->setDb($this->db);
        if(!empty($workerCfg['cacheServers']))
            $authorizeManager->setCache($workerCfg['cacheServers']);

        $authorizeManager->flushAuthorizationRules(false);

        $keepAliveClient->setRouter($this->router);
        $this->loop->addPeriodicTimer(2, [$keepAliveClient, 'sendKeepAlive']);

        $authProvClient = (new AuthProviderClient([$wsRealm]))->setCfg($this->cfg)->setDb($this->db);

        $this->router->registerModules([$authMgr,$authorizeManager]);

        $this->realm = new Realm($wsRealm);
        $this->router->getRealmManager()->setAllowRealmAutocreate(false);
        $this->router->getRealmManager()->addRealm($this->realm);
        $this->router->addInternalClient($authProvClient);

        $this->wsPublisher = new Client($this->realm->getRealmName(),$this->loop);
        $this->router->addInternalClient($this->wsPublisher);

        $this->router->addTransportProvider($internalTransportProvider);
        $this->router->addTransportProvider($transportProvider);

        $authorizeManager->setReady(true);

        $this->startRabbitConnection();
        echo "STARTING!!!!!".PHP_EOL;
        $this->router->start();
    }

    protected function startRabbitConnection(){
        $options = [
            'host'      => $this->cfg->get('rabbitmq')['host'],
            'vhost'     => $this->cfg->get('rabbitmq')['vhost'],
            'user'      => $this->cfg->get('rabbitmq')['user'],
            'password'  => $this->cfg->get('rabbitmq')['password'],
        ];

        (new Async\Client($this->loop, $options))->connect()->then(function (Async\Client $client) {
            echo "AMQ CONNECTED!".PHP_EOL;
            $this->rabbitChannel = $client->channel();
            return $client->channel();
        })->then(function (Channel $channel) {
            $channel->queueDeclare(
                $this->cfg->get('rabbitmq')['queues']['wsCalls'],false,true,false,false)
                ->then(function () use ($channel) {
                    return $channel;
                });
            return $channel;
        })->then(function (Channel $channel) {
            return $channel->qos(0, 1)->then(function () use ($channel) {
                return $channel;
            });
        })->then(function (Channel $channel) {
            echo "CONSUME".PHP_EOL;
            $channel->consume(
                function (Message $message, Channel $channel, Async\Client $client) {
                    try {
                        $this->processWs($message);
                        $channel->ack($message);
                    }catch (\Throwable $e){
                        $channel->nack($message);
                    }
                },
                $this->cfg->get('rabbitmq')['queues']['wsCalls']
            );
        });
    }

    public function processWs(Message $msg) : void{
        try {
            echo $msg->content.PHP_EOL;
            $realm = $this->cfg->get('ws')['realm'];
            $data = json_decode($msg->content,true);
            foreach ($data as $message){
                $publishRealm = $realm.'.'.$message['topic'];
                $this->wsPublisher->getSession()->publish($publishRealm,[$message], [], ["acknowledge" => true]);
            }
        }catch (\Throwable $e){
            throw new \Exception('Cannot publish WS message :('.PHP_EOL.$e->getMessage());
        }
    }
}
