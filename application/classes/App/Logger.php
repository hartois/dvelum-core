<?php
namespace App;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        echo $message.PHP_EOL;
    }
}
