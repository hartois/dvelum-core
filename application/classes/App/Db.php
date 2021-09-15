<?php
declare(strict_types=1);

namespace App;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\ResultSet\ResultSet;

class Db{
    protected ConfigInterface $cfg;
    protected static array $instances = [];
    protected Adapter $adapter;

    protected function __construct(string $name, ConfigInterface $cfg){
        $this->cfg = $cfg;
        $this->adapter = new Adapter($cfg->get('db')[$name]);
    }

    public static function factory(string $name, ?ConfigInterface $cfg = null) : self{
        if(!$cfg)
            $cfg = Config::storage()->get('ccng.php');

        if(!array_key_exists($name, self::$instances))
            self::$instances[$name] = new static($name, $cfg);

        return self::$instances[$name];
    }

    public function getAdapter() {
        return $this->adapter;
    }

    public function getCursors(ResultInterface $result) {
        $cursorsSet = new ResultSet();
        $cursorsSet->initialize($result);
        $cursors = $cursorsSet->toArray();
        if(empty($cursors))
            return [];

        return array_column($cursors,array_keys($cursors[0])[0]);
    }

    public function fetchCursors($result) {
        $data = [];
        $cursors = $this->getCursors($result);
        foreach ($cursors as $cursor){
            $resultSet = new ResultSet();
            $data[$cursor] = $resultSet->initialize(
                $this->adapter->createStatement('fetch all in "'.$cursor.'"')->execute()
            )->toArray();
        }

        return $data;
    }
}
