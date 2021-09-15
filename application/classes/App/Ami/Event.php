<?php
declare(strict_types=1);

namespace App\Ami;

use App\Db;
use App\Logger;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Log\File;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;

class Event {
    protected Adapter $adapter;
    protected Db $db;
    protected $eTable = null;
    protected $eventFields = [];
    protected ConfigInterface $cfg;

    protected ?int $id = null;
    protected string $eventMessage;
    protected $data = [];
    protected $stateData = [];

    protected $logger;

    public static function factory($eventName, $instanceNamespace, ?int $id = null) {
        $className = $instanceNamespace.'\\'.$eventName;
        if(!class_exists($className))
            $className = __NAMESPACE__.'\\'.$eventName;

        echo "ClassName: ".$className.PHP_EOL;

        $unknownClass = !class_exists($className);
        $logger = new Logger();
        if($unknownClass){
            $logger->error('Unknown event type: '.$eventName.PHP_EOL);
        }
        return (!$unknownClass) ? new $className($id) : new static($id);
    }

    public function __construct(?int $id){
        $this->logger = new Logger();
        $this->id = $id;
    }

    public function setDb(Db $db) : self {
        $this->db = $db;
        $this->adapter = $this->db->getAdapter();

        return $this;
    }

    public function setData(array $data) : self {
        echo "EventClass: ".get_called_class().PHP_EOL;
        array_walk($data, function(&$value,$key) {
            $filterMethod = 'filter'.ucfirst(strtolower($key));
            //echo $filterMethod.PHP_EOL;
            if(method_exists($this,$filterMethod))
                $value = $this->{$filterMethod}($value);
        });
        $this->data = $data;
        return $this;
    }

    public function setEventMessage(string $eventMessage) : self {
        $this->eventMessage = $eventMessage;
        return $this;
    }

    public function save() : self {
        try {
            if(!empty($this->eventId))
                $this->update();
            else
                $this->insert();

        }catch (\Throwable $e){
            $this->logger->error($e->getMessage());
        }

        return $this;
    }

    protected function insert() {
        $sql = 'insert into ccng.events(u_time,e_table,instanceuuid) VALUES($1,$2,$3) returning id;';
        $event = $this->adapter->query($sql,[$this->data['uTime'],$this->eTable,$this->data['instanceUuid']])->toArray();
        $this->id = (int)$event[0]['id'];

        try {
            $sql = new Sql($this->adapter);
            $insert = $sql->insert()->into(new TableIdentifier('e_raw', 'ccng'))
                ->columns(['id', 'data'])
                ->values([$this->id, $this->eventMessage]);
            $sql->prepareStatementForSqlObject($insert)->execute();
        }catch (\Throwable $e){
            $this->logger->error('TABLE: '.$this->eTable.PHP_EOL.var_export($this->data));
            throw new \Exception('Cannot process event: '.print_r($this->data,true));
        }

        if(empty($this->eTable))
            return $this->id;

        $fields = (!empty($this->eventFields)) ? array_keys($this->eventFields) : array_keys($this->data);

        $data = [];
        foreach ($fields as $field){
            $dbCol = (!empty($this->eventFields[$field])) ? $this->eventFields[$field]['dbField'] : $field;
            if(!array_key_exists($field,$this->data) || strlen((string)$this->data[$field]) === 0)
                continue;
            $data[$dbCol] = $this->data[$field];
        }
        $data['id'] = $this->id;
        try {
            $insert = $sql->insert()->into(new TableIdentifier($this->eTable,'ccng'))
                ->values($data);
            $sql->prepareStatementForSqlObject($insert)->execute();
        }catch (\Throwable $e){
            $this->logger->error('TABLE: '.$this->eTable.PHP_EOL.var_export($this->data));
            throw new \Exception('Cannot process event: '.print_r($this->data,true));
        }


        /*        $sql = new Sql($this->adapter);
                $EventDt = (new \DateTime($data['EventDt']))->format(DATE_ISO8601);

                $sql->insert()->into(new TableIdentifier($eTable,'ccng'))
                    ->columns(['id','address', 'status', 'dt'])
                    ->values([$status['task_id'],$status['address'], $statusVal, $EventDt]);

                $ret = $sql->prepareStatementForSqlObject($insert)->execute();
        */
        return $this->id;
    }

    protected function processQueueCallState(){
        $sql = 'select * from ccng.process_queue_call_state($1,$2);';
        return $this->adapter->query($sql,[$this->data['Uniqueid'],$this->data['uTime']])->toArray();
    }

    protected function processOutCallState() : array{
        /*$sql = 'select * from ccng.process_out_call_state($1,$2);';
        return $this->adapter->query($sql,[$this->data['Uniqueid'],$this->data['uTime']])->toArray();*/
        //print_r([$this->data['Uniqueid'],$this->data['uTime']]);
        $this->adapter->createStatement('begin;')->execute();
        $sql = 'select * from ccng.process_out_call_state($1,$2,$3,$4) cursor;';
        try {
            $result = $this->adapter->createStatement($sql)->execute([$this->data['Uniqueid'],$this->data['uTime'],'queue_ref','out_ref']);
            $cursorsData = $this->db->fetchCursors($result);
            $this->adapter->createStatement('commit')->execute();
        }catch (\Exception $e){
            $this->adapter->createStatement('rollback')->execute();
            throw new \Exception('Cannot execute: '.$e->getMessage(),0,$e);
        }
        $data = [];
        if(!empty($cursorsData['queue_ref']))
            $data = array_merge($data,$cursorsData['queue_ref']);
        if(!empty($cursorsData['out_ref']))
            $data = array_merge($data,$cursorsData['out_ref']);

        return $data;
    }

    public function getStates() {
        return $this->stateData;
    }

    protected function update(){

    }
}
