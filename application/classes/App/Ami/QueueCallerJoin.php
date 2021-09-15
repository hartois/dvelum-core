<?php


declare(strict_types=1);

namespace App\Ami;

use Laminas\Db\Adapter\Adapter;

class QueueCallerJoin extends Event {
    protected $eTable = 'e_queuecallerjoin';
    protected $eventFields = [
        'Channel' => ['dbField' => 'channel'],
        'ChannelState' => ['dbField' => 'channelstate'],
        'ChannelStateDesc' => ['dbField' => 'channelstatedesc'],
        'CallerIDNum' => ['dbField' => 'calleridnum'],
        'CallerIDName' => ['dbField' => 'calleridname'],
        'ConnectedLineNum' => ['dbField' => 'connectedlinenum'],
        'ConnectedLineName' => ['dbField' => 'connectedlinename'],
        'Language' => ['dbField' => 'language'],
        'AccountCode' => ['dbField' => 'accountcode'],
        'Context' => ['dbField' => 'context'],
        'Exten' => ['dbField' => 'exten'],
        'Priority' => ['dbField' => 'priority'],
        'Uniqueid' => ['dbField' => 'uniqueid'],
        'Linkedid' => ['dbField' => 'linkedid'],
        'Queue' => ['dbField' => 'queue'],
        'Position' => ['dbField' => 'position'],
        'Count' => ['dbField' => 'count'],
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];

    public function insert(){
        $id = parent::insert();
        return $this->adapter->query('select new_queue_call($1)',[$id]);
    }

    public function save(): Event {
        parent::save();
        $this->stateData = $this->processQueueCallState();

        return $this;
    }
}
