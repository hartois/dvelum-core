<?php
declare(strict_types=1);

namespace App\Frontend\Back\Login;

use App\Db;
use App\Exception\AuthFailed;
use App\Exception\BadRequest;
use App\JsonRequest;
use App\User;
use \Dvelum\App\Frontend;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Response;
use Dvelum\Store\Local;
use Dvelum\Store\Session;
use Laminas\Db\Adapter\Adapter;

class Controller extends Frontend\Controller {
    protected JsonRequest $jsonRequest;

    public function __construct(Request $request, Response $response){
//        ini_set('display_errors', 'Off');
        parent::__construct($request, $response);

        if(!empty($_SERVER['CONTENT_TYPE']) && explode(';', $_SERVER['CONTENT_TYPE'])[0] === 'application/json')
            $this->response->setFormat(Response::FORMAT_JSON);

    }

    public function indexAction() {
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
        $template->setData($data);
        $this->response->put($template->render($layoutPath));
    }

    public function loginAction() {
        $this->jsonRequest = new JsonRequest($this->request);
        $login = $this->jsonRequest->bodyParam('login', 'string', null);
        $password = $this->jsonRequest->bodyParam('password', 'string', null);
        //$login = $this->request->get('login', 'string', null);
        //$password = $this->request->get('password', 'string', null);

        if(empty($login) || empty($password))
            throw new BadRequest();

        $db = Db::factory('main');
        $session = new Session('user');
        try {
            $user = User::login($login,$password)->setSession($session)->dbSetAuth($db)->storeAuth($session);
        }catch (\Throwable $e){
            throw new AuthFailed('Auth failed!');
        }
        $this->response->json($user->getUserData());
    }

    public function logoutAction() {
        session_destroy();
        $this->response->success();
    }
}
