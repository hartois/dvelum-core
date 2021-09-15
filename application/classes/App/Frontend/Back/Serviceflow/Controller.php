<?php
declare(strict_types=1);

namespace App\Frontend\Back\Serviceflow;

use App\Exception\BadRequest;
use App\Frontend\Back;
use App\Modules\Forms\Forms;

class Controller extends Back\Controller {
    public function indexAction(){
        $sub = $this->request->getPart(1);
        $serviceflow = ucfirst(strtolower($sub));

        $className = __NAMESPACE__.'\\'.$serviceflow.'\\Controller';
        if(!class_exists($className))
            $className = 'App\\Modules\\Serviceflow\\'.$serviceflow.'\\Frontend\\Controller';

        if(!class_exists($className))
            throw new BadRequest('Wrong Serviceflow "'.$serviceflow.'"!');

        $actionName = $this->request->getPart(2).'Action';
        if(!class_exists($className) || !method_exists($className,$actionName))
            throw new BadRequest('Wrong action!');

        $controller = new $className($this->request, $this->response);
        return $controller->{$actionName}();
    }
}
