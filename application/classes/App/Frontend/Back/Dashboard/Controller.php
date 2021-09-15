<?php
declare(strict_types=1);

namespace App\Frontend\Back\Dashboard;

use App\Db;
use App\User;
use \Dvelum\App\Frontend;
use App\Frontend\Back;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Response;
use Dvelum\Store\Session;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\Pgsql\Result;
use Laminas\Db\ResultSet\ResultSet;

class Controller extends Back\Controller {
    public function callslistAction() {
        $limit = $this->jsonRequest->bodyParam('limit','int',50);
        $offset = $this->jsonRequest->bodyParam('offset','int',0);
        $sql = 'select * from front.get_queue_calls_state($1,$2,$3)';
        $data = $this->adapter->query($sql,[null,null,json_encode(['limit' => $limit, 'offset' => $offset])])->toArray();
        array_walk($data, function (&$item){
            $item['call_dt'] = !empty($item['call_dt'])
                ? (new \DateTime($item['call_dt']))->format('Y.m.d H:i:s')
                : null;
            $item['direction'] = 'queue';
        });
        $this->response->success($data);
    }
}
