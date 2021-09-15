<?php
namespace App\Wamp;
use \Thruway\Peer\Client;
class KeepAliveClient extends Client{
    private $router;
    /**
     * @var \Thruway\ClientSession
     */
    protected $session;
    private $realm = 'ccng';


    public function setRealm(string $realm) : self{
        $this->realm = $realm;
        return $this;
    }

    public function onSessionStart($session, $transport){
        $this->session = $session;
    }

    public function sendKeepAlive(){
        if($this->router === null){
            throw new \Exception("Router must be set before calling ping.");
        }

        if(!$this->session){
            return false;
        }

        $this->session->publish($this->realm,[['type'=>'keepalive']], [], ["acknowledge" => true]);

    }

    public function setRouter($router){
        $this->router = $router;
    }
}
