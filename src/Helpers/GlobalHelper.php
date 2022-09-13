<?php

namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;

class GlobalHelper
{
    public static array $whiteListIps = ['127.0.0.1'];

    public static function getCurrentMicroTime(): float
    {
        return round(microtime(true) * 1000);
    }

    public static function parseClientIp(ServerRequestInterface $request): array
    {
        $serverParams = $request->getServerParams();
        $headerParams = $request->getHeaders();

        if (!empty($serverParams['HTTP_CLIENT_IP'])) {
            $ip = $serverParams['HTTP_CLIENT_IP'];
        } else if (!empty($headerParams['X-Real-Ip'][0])) {
            $ip = explode(",", $headerParams['X-Real-Ip'][0])[0];
        } else if (!empty($headerParams['X-Forwarded-For'][0])) {
            $ip = explode(",", $headerParams['X-Forwarded-For'][0])[0];
        } else {
            $ip = $serverParams['REMOTE_ADDR'];
        }

        if (self::validateIp($ip))
            return [
                'result' => true,
                'ip' => $ip
            ];

        return ['result' => false];
    }

    public static function validateIp($ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false)
            return false;

        return true;
    }

    public static function generateUUID(): array
    {
        try {
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return [
                'result' => true,
                'hash' => vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4))
            ];
        } catch (\Exception $e) {
            return ['result' => false];
        }
    }
}