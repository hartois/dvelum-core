<?php


declare(strict_types=1);

namespace App\Ami;

class BlindTransfer extends Event {
    protected $eTable = 'e_blindtransfer';
    protected $eventFields = [
        'Result' => ['dbField' => 'result'],
        'TransfererChannel' => ['dbField' => 'transfererchannel'],
        'TransfererChannelState' => ['dbField' => 'transfererchannelstate'],
        'TransfererChannelStateDesc' => ['dbField' => 'transfererchannelstatedesc'],
        'TransfererCallerIDNum' => ['dbField' => 'transferercalleridnum'],
        'TransfererCallerIDName' => ['dbField' => 'transferercalleridname'],
        'TransfererConnectedLineNum' => ['dbField' => 'transfererconnectedlinenum'],
        'TransfererConnectedLineName' => ['dbField' => 'transfererconnectedlinename'],
        'TransfererLanguage' => ['dbField' => 'transfererlanguage'],
        'TransfererAccountCode' => ['dbField' => 'transfereraccountcode'],
        'TransfererContext' => ['dbField' => 'transferercontext'],
        'TransfererExten' => ['dbField' => 'transfererexten'],
        'TransfererPriority' => ['dbField' => 'transfererpriority'],
        'TransfererUniqueid' => ['dbField' => 'transfereruniqueid'],
        'TransfererLinkedid' => ['dbField' => 'transfererlinkedid'],
        'TransfereeChannel' => ['dbField' => 'transfereechannel'],
        'TransfereeChannelState' => ['dbField' => 'transfereechannelstate'],
        'TransfereeChannelStateDesc' => ['dbField' => 'transfereechannelstatedesc'],
        'TransfereeCallerIDNum' => ['dbField' => 'transfereecalleridnum'],
        'TransfereeCallerIDName' => ['dbField' => 'transfereecalleridname'],
        'TransfereeConnectedLineNum' => ['dbField' => 'transfereeconnectedlinenum'],
        'TransfereeConnectedLineName' => ['dbField' => 'transfereeconnectedlinename'],
        'TransfereeLanguage' => ['dbField' => 'transfereelanguage'],
        'TransfereeAccountCode' => ['dbField' => 'transfereeaccountcode'],
        'TransfereeContext' => ['dbField' => 'transfereecontext'],
        'TransfereeExten' => ['dbField' => 'transfereeexten'],
        'TransfereePriority' => ['dbField' => 'transfereepriority'],
        'TransfereeUniqueid' => ['dbField' => 'transfereeuniqueid'],
        'TransfereeLinkedid' => ['dbField' => 'transfereelinkedid'],
        'BridgeUniqueid' => ['dbField' => 'bridgeuniqueid'],
        'BridgeType' => ['dbField' => 'bridgetype'],
        'BridgeTechnology' => ['dbField' => 'bridgetechnology'],
        'BridgeCreator' => ['dbField' => 'bridgecreator'],
        'BridgeName' => ['dbField' => 'bridgename'],
        'BridgeNumChannels' => ['dbField' => 'bridgenumchannels'],
        'BridgeVideoSourceMode' => ['dbField' => 'bridgevideosourcemode'],
        'IsExternal' => ['dbField' => 'isexternal'],
        'Context' => ['dbField' => 'context'],
        'Extension' => ['dbField' => 'extension'],
        'EventDt' => ['dbField' => 'eventdt'],
        'uTime' => ['dbField' => 'utime'],
        'instanceUuid' => ['dbField' => 'instanceuuid'],
    ];
}
