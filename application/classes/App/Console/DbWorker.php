<?php
declare(strict_types=1);

namespace App\Console;
use App\Db;
use App\User;
use Dvelum\App\Console;
use Laminas\Db\Adapter\Adapter;

class DbWorker extends Console\Action {

    protected Db $db;
    protected Adapter $adapter;

    protected function initDbConnect(string $authKey) {
        $this->db = Db::factory('main');
        User::factory(1)->setAuthKey($authKey)->dbSetAuth($this->db);
        $this->adapter = $this->db->getAdapter();
    }

    protected function action(): bool { return true; }
}
