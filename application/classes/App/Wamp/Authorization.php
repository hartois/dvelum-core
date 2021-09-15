<?php
/**
 *  Part of CCNG
 *  Copyright (C) 2013-2021  Sergey Leschenko - hartois@gmail.com
 */

namespace App\Wamp;

use App\Db;
use Dvelum\Cache\AbstractAdapter;
use Dvelum\Cache\Memcached;
use Thruway\Authentication\AuthorizationManager;
use Thruway\Message\ActionMessageInterface;
use Thruway\Message\PublishMessage;
use Thruway\Message\SubscribeMessage;
use Thruway\Session;

class Authorization extends AuthorizationManager{
    protected Db $db;
    protected ?Memcached $cache = null;

    public function setCache(array $servers) : self {
        $this->cache = new Memcached([
            'servers' => $servers,
            'defaultLifeTime' => 60,
            'keyPrefix' => 'ccng_'
        ]);
        return $this;
    }

    public function setDb(Db $db) : self {
        $this->db = $db;
        return $this;
    }

    public function isAuthorizedTo(Session $session, ActionMessageInterface $actionMsg){
        if($session->getTransport()->isTrusted())
            return true;

        if($actionMsg instanceof SubscribeMessage
            && $actionMsg->getTopicName() == 'ccng'
            && $session->isAuthenticated()
            && !empty($session->getAuthenticationDetails()->getAuthId())
        )
            return true;

        if($actionMsg instanceof PublishMessage &&
            $actionMsg->getActionName() == 'publish'
            && $session->getAuthenticationDetails()->hasAuthRole('publisher_full'))
            return true;

        if($actionMsg instanceof SubscribeMessage
           && $actionMsg->getTopicName() == 'ccng.user.'.$session->getAuthenticationDetails()->getAuthId()

        )
            return true;

        $authId = $session->getAuthenticationDetails()->getAuthId();
        $cacheId = 'queues_user_'.$authId;

        $allowedQueuesTopics = ($this->cache instanceof Memcached) ? $this->cache->load($cacheId) : false;
        if($allowedQueuesTopics === false) {
            $allowedQueues = $this->db->getAdapter()
                ->query('select * from front.get_user_queues($1)', [$authId])
                ->toArray();
            $allowedQueuesTopics = array_map(function ($item) {
                return 'ccng.call.queue.' . $item['queue_id'];
            }, $allowedQueues);

            $allowedQueuesTopics = serialize($allowedQueuesTopics);

            if($this->cache instanceof Memcached)
                $this->cache->save($allowedQueuesTopics,$cacheId);
        }
        $allowedQueuesTopics = unserialize($allowedQueuesTopics);

        if(
            $actionMsg instanceof SubscribeMessage
            && in_array($actionMsg->getTopicName(), $allowedQueuesTopics)
        )
            return true;

        return parent::isAuthorizedTo($session, $actionMsg);
    }
}
