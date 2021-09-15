<?php


declare(strict_types=1);

namespace App\Ami;

class DialEnd extends Event {
    protected $eTable = 'e_dialend';
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
        'DialStatus' => ['dbField' => 'dialstatus'],
        'Forward' => ['dbField' => 'forward'],
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];

    public function save(): Event {
        parent::save();
        if(!empty($this->data['Uniqueid']))
            $this->stateData = $this->processOutCallState();

        return $this;
    }
}
