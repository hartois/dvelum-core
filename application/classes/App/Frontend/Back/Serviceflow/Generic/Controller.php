<?php
declare(strict_types=1);

namespace App\Frontend\Back\Serviceflow\Generic;

use App\Frontend\Back;

class Controller extends Back\Controller {
    public function loadflowuiAction() {
        $class = $this->request->get('class', 'string', false);
        $uiPath = $this->appConfig->get('docRoot').'/application/ui/panel/src/views/serviceflow/classes/'.$class.'.vue';
        $this->response->put(file_get_contents($uiPath));
        $this->response->send();
    }

    public function getmdcontentAction() {
        $path = $this->jsonRequest->bodyParam('path', 'string', false);

        $mdFilePath = '/serviceflow/'.$path;
        $template = \Dvelum\View::factory();
        $template->disableCache();

        $data = [
            'subscriberName' => 'Иванов Иван Иванович'
        ];

        $template->setData($data);
        $mdContent = $template->render($mdFilePath);

        $this->response->json(['content' => $mdContent]);
    }
}
