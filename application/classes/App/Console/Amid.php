<?php

declare(strict_types=1);

namespace App\Console;

use Clue\React\Ami;
use Dvelum\App\Console;
use Dvelum\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection as AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Socket;

class Amid extends Console\Action
{
    protected bool $amiInitialized = false;
    protected $stopFlag = false;
    protected $reloadFlag = false;

    private Config\ConfigInterface $cfg;
    private AMQPSocketConnection $rabbitConnection;
    private AMQPChannel $rabbitChannel;
    private array $rabbitCfg;
    protected ?string $instanceUuid = null;
    protected int $keepAlive;
    protected TimerInterface $pingTimer;
    protected TimerInterface $kaTimer;

    private $dnsServer;

    private $amiHost;
    private $amiScheme;
    private $amiPort;
    private $amiUsername;
    private $amiSecret;
    private $connectTimeout;

    protected Ami\Client $amiClient;
    protected LoopInterface $loop;
    protected Ami\Factory $amiFactory;

    private $eventFilters = [
        'QueueCallerJoin',
        'QueueCallerLeave',
        'QueueCallerAbandon',
        'AgentDump',
        'AgentCalled',
        'AgentComplete',
        'AgentRingNoAnswer',
        'AgentConnect',
        'AttendedTransfer',
        'BlindTransfer',
        'DialBegin',
        'DialEnd',
        'BridgeEnter',
        'BridgeLeave',
        'Hangup',
        'UserEvent',
        'MixMonitorStart',
        'Newchannel',
        'CEL'
    ];

    private $eventTypesFilter = [
        'call',
        'log',
        'agent',
        'originate',
        'cdr',
        'cel',
        'system'
    ];

    public function stop(){
        $this->stopFlag = true;
    }

    public function reload(){
        $this->reloadFlag = true;
    }

    protected function setAmiOptions($options) {
        $this->amiHost = $options['amiHost'];
        $this->amiScheme = $options['amiScheme'];
        $this->amiPort = $options['amiPort'];
        $this->amiUsername = $options['amiUsername'];
        $this->amiSecret = $options['amiSecret'];
        $this->connectTimeout = $options['connectTimeout'];
    }

    public function action(): bool {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'stop']);
        pcntl_signal(SIGHUP, [$this, 'reload']);

        if(!empty($this->params))
            $this->instanceUuid = $this->params[0];

        $workerCfgFile = $_SERVER['argv'][2];
        if(!file_exists($workerCfgFile))
            throw new \Exception('Wrong worker cfg file: '.$workerCfgFile);

        $workerCfg = require $workerCfgFile;
        $this->setAmiOptions($workerCfg['amiOptions']);

        if(!empty($workerCfg['dnsServer']))
            $this->dnsServer = $workerCfg['dnsServer'];

        $this->cfg = Config::storage()->get('ccng.php');
        $this->loop = Loop::get();
        $this->connectRabbit();
        $this->start();

        return true;
    }

    protected function connectRabbit() : bool {
        $this->rabbitCfg = $this->cfg->get('rabbitmq');
        $this->rabbitConnection = new AMQPConnection(
            $this->rabbitCfg['host'], $this->rabbitCfg['port'],
            $this->rabbitCfg['user'], $this->rabbitCfg['password'], $this->rabbitCfg['vhost']
        );

        $eventsQueueName = $this->rabbitCfg['queues']['amiEvents'];

        $this->rabbitChannel = $this->rabbitConnection->channel();
        $this->rabbitChannel->queue_declare($eventsQueueName,false,true,false,false);
        $this->rabbitChannel->basic_qos(0,1,true);

        return true;
    }

    protected function connectAmi() : bool {
        echo "-------------> CONNECT AMI".PHP_EOL;
        $connectOptions = [
            'timeout' => $this->connectTimeout
        ];
        if($this->dnsServer)
            $connectOptions['dns'] = $this->dnsServer;

        $reactConnector = new Socket\Connector($this->loop, $connectOptions);
        $this->amiFactory = new Ami\Factory($this->loop, $reactConnector);

        $amiUrl = $this->amiScheme.$this->amiUsername.':'.$this->amiSecret.'@'.$this->amiHost.':'.$this->amiPort;
        $this->amiFactory->createClient($amiUrl)->then(function (Ami\Client $client) {
            echo 'Client connected' . PHP_EOL;
            $this->amiClient = $client;
            $this->amiClient->on('close',function (Ami\Client $client){
                $client->removeAllListeners();
                $this->loop->cancelTimer($this->pingTimer);
                $this->loop->cancelTimer($this->kaTimer);
                $this->amiInitialized = false;
                unset($this->amiClient);
                $this->connectAmi();
            });

            $this->amiClient->on('event', function (Ami\Protocol\Event $event) {
                // process an incoming AMI event (see below)
                if (in_array($event->getName(), $this->eventFilters)){
                    $this->queueEvent($event);
                }else {
                    if(!in_array($event->getName(),['Newexten','RTCPSent','RTCPReceived','VarSet']))
                        echo "Filtered Event: " . $event->getName() . PHP_EOL;
                    if(in_array($event->getName(),['CEL','Cdr','Newchannel']))
                        print_r($event->getFields());
                }
            });

            $this->keepAlive = time();
            if(!$this->amiInitialized) {
                echo "SET PERIODIC TIMER".PHP_EOL;
                $this->pingTimer = $this->loop->addPeriodicTimer(3, function () {
                    $sender = new Ami\ActionSender($this->amiClient);
                    $sender->events($this->eventTypesFilter);

                    $sender->ping()->then(function () {
                        echo "-------> Ping: OK" . PHP_EOL;
                        $this->keepAlive = time();
                    }, function () {
                        echo "-------> Ping: FAIL" . PHP_EOL;
                        $this->amiClient->close();
                    });
                });

                $this->kaTimer = $this->loop->addPeriodicTimer(5, function () {
                    if(!$this->amiInitialized || time() - $this->keepAlive <= 3 )
                        return;

                    echo "-------> Keepalive: FAIL" . PHP_EOL;
                    $this->amiClient->close();
                });
            }
            $this->amiInitialized = true;
        },function (\Exception $e) {
            echo $e->getMessage().PHP_EOL;
        });

        return true;
    }

    protected function start(){
        while (!$this->stopFlag) {
            Loop::stop();

            $this->connectAmi();
            $this->loop->run();
        }
        return true;
    }

    public function queueEvent(Ami\Protocol\Event $event){
        echo "Event: ".$event->getName().PHP_EOL;
        print_r($event->getFields());

        $queueName = $this->rabbitCfg['queues']['amiEvents'];

        $data = array_merge($event->getFields(),
            [
                'instanceUuid' => $this->instanceUuid,
                'EventDt' => (new \DateTime())->format(DATE_ISO8601),
                'uTime' => round(microtime(true) * 1000000)
            ]
        );
        $statusMsg = new AMQPMessage(json_encode($data), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->rabbitChannel->basic_publish($statusMsg, '', $queueName);
    }
}
