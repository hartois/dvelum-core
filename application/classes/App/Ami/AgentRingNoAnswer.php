<?php


declare(strict_types=1);

namespace App\Ami;

class AgentRingNoAnswer extends Event {
    protected $eTable = 'e_agentringnoanswer';
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
        'DestChannel' => ['dbField' => 'destchannel'],
        'DestChannelState' => ['dbField' => 'destchannelstate'],
        'DestChannelStateDesc' => ['dbField' => 'destchannelstatedesc'],
        'DestCallerIDNum' => ['dbField' => 'destcalleridnum'],
        'DestCallerIDName' => ['dbField' => 'destcalleridname'],
        'DestConnectedLineNum' => ['dbField' => 'destconnectedlinenum'],
        'DestConnectedLineName' => ['dbField' => 'destconnectedlinename'],
        'DestLanguage' => ['dbField' => 'destlanguage'],
        'DestAccountCode' => ['dbField' => 'destaccountcode'],
        'DestContext' => ['dbField' => 'destcontext'],
        'DestExten' => ['dbField' => 'destexten'],
        'DestPriority' => ['dbField' => 'destpriority'],
        'DestUniqueid' => ['dbField' => 'destuniqueid'],
        'DestLinkedid' => ['dbField' => 'destlinkedid'],
        'Queue' => ['dbField' => 'queue'],
        'Interface' => ['dbField' => 'interface'],
        'MemberName' => ['dbField' => 'membername'],
        'RingTime' => ['dbField' => 'ringtime'],
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];
}
