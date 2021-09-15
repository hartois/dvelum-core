<?php
/**
 *  Part of Call Center Panel module for DVelum project
 *  Copyright (C) 2013-2020  Sergey Leschenko - hartois@gmail.com
 */

namespace App\Wamp;

use App\Db;
use \Thruway\Authentication\AbstractAuthProviderClient;
use App\Wamp;

/**
 * Class SimpleAuthProviderClient
 */
class AuthProviderClient extends AbstractAuthProviderClient
{
    protected Db $db;
    public $wampSession;
    private $cfg;

    public function setDb(Db $db) : self {
        $this->db = $db;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethodName(){
        return 'ccng';
    }

    public function setCfg($cfg){
        $this->cfg = $cfg;
        return $this;
    }

    /**
     * Process Authenticate message
     *
     * @param mixed $signature
     * @param mixed $extra
     * @return array
     */
    public function processAuthenticate($sessionId = null, $extra = null){
        try {
            $this->wampSession = (new Wamp\Session($sessionId))->setDb($this->db)->loadData();
        }catch (\Throwable $e){
            echo "WRONG AUTH: ".$e->getMessage().PHP_EOL;
            return ["FAILURE"];
        }

        if($this->wampSession->get('id')) {
            $authId = $this->wampSession->get('id');

            return ['SUCCESS', [
                'authmethod' => 'ccng',
                'authroles' => $this->wampSession->getRoles(),
                'queues' => $this->wampSession->getQueues(),
                'authid' => $authId
            ]];
        }

        return ["FAILURE"];
    }
}
