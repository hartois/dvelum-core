<?php

declare(strict_types=1);

namespace App\Exception;

class AuthFailed extends \Exception implements ExceptionInterface {
    protected $message = 'Auth failed';

    public function getHttpCode() : int {
        return 401;
    }
}
