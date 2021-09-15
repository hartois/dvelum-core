<?php

declare(strict_types=1);

namespace App\Exception;

class Forbidden extends \Exception implements ExceptionInterface {
    protected $message = 'Forbidden';

    public function getHttpCode() : int {
        return 403;
    }
}
