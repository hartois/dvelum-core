<?php
declare(strict_types=1);

namespace App;

use Dvelum\Store\Session;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\ResultSet\ResultSet;

class User{
    protected static array $instances = [];
    protected string $authKey;
    protected int $id;
    protected $userData = [];
    protected static $userFields = ['id','login','email'];

    public function __construct(int $id){
        $this->id = $id;
    }

    public static function factory(int $id){
        if(!array_key_exists($id, self::$instances))
            self::$instances[$id] = new static($id);

        return self::$instances[$id];
    }

    public static function login($login, $password) : self {
        $adapter = Db::factory('main')->getAdapter();
        $sql = 'select * from ccng.user_login($1,$2)';
        try {
            $data = $adapter->query($sql,[$login,$password])->toArray();
            if(empty($data))
                throw new \Exception('Authentification failed!');
            $data = $data[0];
        }catch (\Throwable $e){
            throw new \Exception('Authentification failed!');
        }
        self::$instances[$data['id']] = new static((int)$data['id']);
        self::$instances[$data['id']]->setAuthKey($data['auth_key']);

        $userData = array_intersect_key($data, array_flip(static::$userFields));
        $userData['session_id'] = session_id();
        $userData['data'] = (!empty($data['data'])) ? json_decode($data['data'],true) : [];
        self::$instances[$data['id']]->setUserData($userData);

        return self::$instances[$data['id']];
    }

    public function loadMyData() : self{
        $myData = Db::factory('main')->getAdapter()
            ->query("select * from front.get_my_data()", Adapter::QUERY_MODE_EXECUTE)
            ->toArray();

        if(empty($myData))
            throw new \Exception('Wrong user data');

        $myData = $myData[0];

        $this->userData['data'] = json_decode($myData['data'],true);

        return $this;
    }

    public function setUserData(array $data) : self{
        $this->userData = $data;
        return $this;
    }

    public function getUserData() : array {
        return $this->userData;
    }

    public function setAuthKey(string $authKey) : self {
        $this->authKey = $authKey;
        return $this;
    }

    public function setSession(Session $session) : self {
        $authKey = $session->get('authKey');
        $this->session = $session;
        if($authKey)
            $this->setAuthKey($authKey);
        return $this;
    }

    public function dbSetAuth(Db $db) : self {
        $db->getAdapter()->query("select * from front.user_setauthkey($1)", [$this->authKey]);
        return $this;
    }

    public function storeAuth(Session $session) : self {
        $session->set('authKey',$this->authKey);
        $session->set('id',$this->id);
        return $this;
    }
}
