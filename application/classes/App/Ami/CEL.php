<?php


declare(strict_types=1);

namespace App\Ami;

class CEL extends Event {
    protected $eTable = 'e_cel';
    protected $eventFields = [
        'EventName' => ['dbField' => 'eventname'],
        'AccountCode' => ['dbField' => 'accountcode'],
        'Channel' => ['dbField' => 'channel'],
        'CallerIDNum' => ['dbField' => 'calleridnum'],
        'CallerIDName' => ['dbField' => 'calleridname'],
        'CallerIDani' => ['dbField' => 'calleridani'],
        'CallerIDrdnis' => ['dbField' => 'calleridrdnis'],
        'CallerIDdnid' => ['dbField' => 'calleriddnid'],
        'Exten' => ['dbField' => 'exten'],
        'Context' => ['dbField' => 'context'],
        'Application' => ['dbField' => 'application'],
        'AppData' => ['dbField' => 'appdata'],
        'EventTime' => ['dbField' => 'eventtime'],
        'AMAFlags' => ['dbField' => 'amaflags'],
        'UniqueID' => ['dbField' => 'uniqueid'],
        'LinkedID' => ['dbField' => 'linkedid'],
        'UserField' => ['dbField' => 'userfield'],
        'Peer' => ['dbField' => 'peer'],
        'PeerAccount' => ['dbField' => 'peeraccount'],
        'Extra' => ['dbField' => 'extra'],
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];

    public function filterEventtime($value){
        return (new \DateTime($value))->format(DATE_ISO8601);
    }

    /*public function save(): Event {
        parent::save();
        if(!empty($this->data['Uniqueid']))
            $this->stateData = $this->processOutCallState();

        return $this;
    }*/
}
