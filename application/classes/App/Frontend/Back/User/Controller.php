<?php
declare(strict_types=1);

namespace App\Frontend\Back\User;

use App\Db;
use App\User;
use \Dvelum\App\Frontend;
use App\Frontend\Back;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Response;
use Dvelum\Store\Session;
use Laminas\Db\Adapter\Adapter;

class Controller extends Back\Controller {
    public function dataAction() {
        $this->user->loadMyData();
        $this->response->json($this->user->getUserData());
    }

    public function getmodulesAction() {
        $this->response->success([[
            'name' => 'recruting', 'path' => '/module/recruting', 'iconClass' => 'el-icon-s-custom', 'i18n' => [
                'ru' => [ 'module' => [ 'recruting' => [ 'name' => 'Рекрутинг' ] ] ]
            ]
        ]]);
    }
}
