<?php

namespace App\Helpers;

class GlobalHelper
{
    public static function getCurrentMicroTime(): float
    {
        return round(microtime(true) * 1000);
    }

    public static function generateRandomOtp(): float
    {
        return rand(11111, 99999);
    }

    public static function parseClientIp(array $serverParams): string
    {
        if (!empty($serverParams['HTTP_CLIENT_IP'])) {
            return $serverParams['HTTP_CLIENT_IP'];
        }
        if (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
            return $serverParams['HTTP_X_FORWARDED_FOR'];
        }
        return $serverParams['REMOTE_ADDR'];
    }
}