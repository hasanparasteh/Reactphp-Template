<?php

namespace App\Helpers;

class Logger
{

    public static function file($message): void
    {
        $filename = dirname(__FILE__) . '../../../logger.log';
        file_put_contents($filename, $message . PHP_EOL, FILE_APPEND);
    }

    public static function console($msg)
    {
        echo "Logger: " . json_encode($msg, 128) . PHP_EOL;
    }
}