<?php


declare(strict_types=1);

namespace App\Ami;

class MixMonitorStart extends Event {
    protected $eTable = 'e_mixmonitorstart';
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
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];

    /*public function save(): Event {
        parent::save();
        if(!empty($this->data['Uniqueid']))
            $this->stateData = $this->processOutCallState();

        return $this;
    }*/
}
