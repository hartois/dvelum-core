<?php

declare(strict_types=1);

namespace App\Exception;

class BadRequest extends \Exception implements ExceptionInterface {
    protected $message = 'Bad Request';

    public function getHttpCode() : int {
        return 400;
    }
}
