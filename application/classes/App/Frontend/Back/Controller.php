<?php
declare(strict_types=1);

namespace App\Frontend\Back;

use App\Db;
use App\Exception\AuthFailed;
use App\JsonRequest;
use App\User;
use \Dvelum\App\Frontend;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Response;
use Dvelum\Store\Session;
use Laminas\Db\Adapter\Adapter;

class Controller extends Frontend\Controller {
    protected Adapter $adapter;
    protected User $user;
    protected JsonRequest $jsonRequest;

    public function __construct(Request $request, Response $response){
        ini_set('display_errors', 'Off');
        parent::__construct($request, $response);

        $this->jsonRequest = new JsonRequest($this->request);
        if(!empty($_SERVER['CONTENT_TYPE']) && explode(';', $_SERVER['CONTENT_TYPE'])[0] === 'application/json')
            $this->response->setFormat(Response::FORMAT_JSON);

        $session = new Session('user');
        $userId = $session->get('id');
        $db = Db::factory('main');

        $action = $this->request->getPart(count($this->request->getPathParts()) - 1);
        if($userId) {
            $this->user = User::factory($userId)->setSession($session)->dbSetAuth($db);
            $this->adapter = $db->getAdapter();
            return;
        }
        if(!empty($action) && !strtolower((string)$action) !== 'index') {
            throw new AuthFailed();
        }
    }

    public function indexAction() {
        /*$action = $this->request->getPart(count($this->request->getPathParts()) - 1);
        $actionMethod = strtolower((string)$action).'Action';
        if(!empty($action) && method_exists($this, $actionMethod))
            return $this->{$actionMethod}();
        */

        header('Content-Type: text/html; charset=utf-8');
        $this->page->setTemplatesPath('panel/');

        $layoutPath = $this->page->getThemePath() . 'layout.php';
        $resource = Resource::factory();

        $template = \Dvelum\View::factory();
        $template->disableCache();

        $data = [
            'development' => $this->appConfig->get('development'),
            'page' => $this->page,
            'path' => $this->page->getThemePath(),
            'resource' => $resource
        ];

        $session = new Session('user');
        if($session->keyExists('id') && $session->get('id')){
            $resource->addInlineJs('window.userId = "'.$session->get('id').'";
            window.sessionId = "'.session_id().'";');
        }
        $template->setData($data);
        $this->response->put($template->render($layoutPath));
    }
}
