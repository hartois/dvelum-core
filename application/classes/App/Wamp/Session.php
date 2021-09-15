<?php

namespace App\Wamp;

use App\Db;

class Session {
    protected Db $db;
    protected $data = [];
    protected $id;
    protected $protectedKeys = [];
    protected $queues = [];
    protected static $sessionName = 'user';
    protected static $sessionPrefix = 'sc_';

    public function __construct($id,$options = []){
        $this->id = $id;

        if(!empty($options['protectedKeys']))
            $this->protectedKeys = $options['protectedKeys'];
    }

    public function setDb(Db $db) : self {
        $this->db = $db;
        return $this;
    }

    public function loadData() : self {
        session_id($this->id);
        session_start();
        if(empty($_SESSION)){
            session_write_close();
            throw new \Exception('Wrong session!');
        }
        $this->data = $_SESSION[self::$sessionPrefix][self::$sessionName];
        session_write_close();

        $this->queues = array_column($this->db->getAdapter()
            ->query('select * from front.get_user_queues($1)',[$this->data['id']])
            ->toArray(),'queue_id');

        return $this;
    }

    public function getData(){
        return $this->data;
    }

    public function getQueues(){
        return $this->queues;
    }

    public function setProtected($key){
        if(!in_array($key,$this->protectedKeys))
            $this->protectedKeys[] = $key;
    }

    public function get($key){
        if(in_array($key,$this->protectedKeys))
            throw new \Exception('Cannot access protected key!');

        if(!array_key_exists($key,$this->data))
            return null;

        return $this->data[$key];
    }

    public function getRoles(){
        $roles = ['user'];

        return $roles;
    }
}
