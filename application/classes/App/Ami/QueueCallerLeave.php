<?php


declare(strict_types=1);

namespace App\Ami;

class QueueCallerLeave extends Event {
    protected $eTable = 'e_queuecallerleave';
    protected $eventFields = [
        'Channel' => ['dbField' => 'channel'],
        'ChannelState' => ['dbField' => 'channelstate'],
        'ChannelStateDesc' => ['dbField' => 'channelstatedesc'],
        'CallerIDNum' => ['dbField' => 'calleridnum', 'filter' => 'calleridnumexternal'],
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
}
