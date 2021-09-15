<?php
declare(strict_types=1);

namespace App\Router\Config;

use Dvelum\App\Router\Config;
use Dvelum\Request;
use Dvelum\Response;
use App\Exception;

class WrappedExceptions extends Config{
    public function route(Request $request, Response $response): void
    {
        try {
            parent::route($request, $response);
        }catch (\Throwable $e){
            $response->setResponseCode(($e instanceof Exception\ExceptionInterface) ? $e->getHttpCode() : 500);
            $response->put($e->getMessage());
            $response->send();
        }
    }
};
