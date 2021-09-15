<?php
declare(strict_types=1);

namespace App\Console;

use App\Ami\Event;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSocketConnection as AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmiEventHandler extends DbWorker
{
    protected ConfigInterface $cfg;
    protected array $rabbitCfg;

    /**
     * @var AMQPChannel
     */
    protected $rabbitChannel;
    protected $rabbitConnection;

    public function action(): bool
    {
        $this->cfg = Config::storage()->get('ccng.php');

        $workerCfgFile = $_SERVER['argv'][2];
        if(!file_exists($workerCfgFile))
            throw new \Exception('Wrong worker cfg file: '.$workerCfgFile);

        $workerCfg = require $workerCfgFile;

        $this->rabbitCfg = $this->cfg->get('rabbitmq');

        $this->initDbConnect($workerCfg['authkey']);

        $this->rabbitConnection = new AMQPConnection(
            $this->rabbitCfg['host'], $this->rabbitCfg['port'],
            $this->rabbitCfg['user'], $this->rabbitCfg['password'], $this->rabbitCfg['vhost']
        );

        $eventsQueueName = $this->rabbitCfg['queues']['amiEvents'];
        $wsCallsQueueName = $this->rabbitCfg['queues']['wsCalls'];

        $this->rabbitChannel = $this->rabbitConnection->channel();

        $this->rabbitChannel->queue_declare($eventsQueueName,false,true,false,false);
        $this->rabbitChannel->basic_qos(0,1,true);

        $this->rabbitChannel->queue_declare($wsCallsQueueName,false,true,false,false);
        $this->rabbitChannel->basic_qos(0,1,true);

        $this->rabbitChannel->basic_consume($eventsQueueName,
            '',false,false,false,false, [$this, 'processTask']);

        while(count($this->rabbitChannel->callbacks)) { // && !$this->reloadFlag && !$this->stopFlag) {
            $this->rabbitChannel->wait();
        }

        $this->rabbitChannel->close();
        $this->rabbitConnection->close();

        return true;
    }

    public function processTask(AMQPMessage $msg) {
        try {
            $this->saveEvent($msg);
        }catch (\Throwable $e){
            echo $e->getMessage();
            //$this->rabbitChannel->basic_nack($msg->getDeliveryTag());
            throw new \Exception('Cannot save messsage!'.PHP_EOL.$e->getMessage());
        }

        $this->rabbitChannel->basic_ack($msg->getDeliveryTag());
    }

    protected function saveEvent(AMQPMessage $msg) : void {
        $data = json_decode($msg->getBody(),true);
        $instanceCfg = (!empty($data['instanceUuid']))
            ? Config::storage()->get('instances/'.$data['instanceUuid'].'.php')
            : Config::storage()->get('instances/default.php');

        $instanceNamespace = $instanceCfg->get('namespace');
        echo "---------\ninstanceNamespace: ".$instanceNamespace.PHP_EOL;

        $obj = Event::factory($data['Event'],$instanceNamespace)
            ->setDb($this->db)
            ->setData(json_decode($msg->getBody(),true))
            ->setEventMessage($msg->getBody())
            ->save();

        if(!empty($obj->getStates()))
            $this->sendToWS($obj->getStates());
    }

    protected function sendToWS(array $calls){
        print_r($calls);
        $queueName = $this->rabbitCfg['queues']['wsCalls'];
        $data = [];
        foreach ($calls as $call){
            $call['topic'] = ($call['direction'] === 'queue')
                ? 'call.'.$call['direction'].'.'.$call['queue']
                :'call.'.$call['direction'];
            $data[] = $call;
            if(!empty($call['agent'])){
                $call['topic'] = 'user.'.$call['agent'];
                $data[] = $call;
            }
        }unset($call);

        $statusMsg = new AMQPMessage(json_encode($data), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->rabbitChannel->basic_publish($statusMsg, '', $queueName);
    }
}
