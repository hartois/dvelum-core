<?php


declare(strict_types=1);

namespace App\Ami;

class BridgeEnter extends Event {
    protected $eTable = 'e_bridgeenter';
    protected $eventFields = [
        'BridgeUniqueid' => ['dbField' => 'bridgeuniqueid'],
        'BridgeType' => ['dbField' => 'bridgetype'],
        'BridgeTechnology' => ['dbField' => 'bridgetechnology'],
        'BridgeCreator' => ['dbField' => 'bridgecreator'],
        'BridgeName' => ['dbField' => 'bridgename'],
        'BridgeNumChannels' => ['dbField' => 'bridgenumchannels'],
        'BridgeVideoSourceMode' => ['dbField' => 'bridgevideosourcemode'],
        'BridgeVideoSource' => ['dbField' => 'bridgevideosource'],
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
        'SwapUniqueid' => ['dbField' => 'swapuniqueid'],
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];

    public function save(): Event {
        parent::save();
        $this->stateData = $this->processQueueCallState();

        return $this;
    }
}
