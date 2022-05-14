<?php

namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;

class GlobalHelper
{
    public static function getCurrentMicroTime(): float
    {
        return round(microtime(true) * 1000);
    }

    public static function parseClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        $headerParams = $request->getHeaders();

        if (!empty($serverParams['HTTP_CLIENT_IP'])) {
            return $serverParams['HTTP_CLIENT_IP'];
        }
        if (!empty($headerParams['X-Forwarded-For'][0])) {
            return $headerParams['X-Forwarded-For'][0];
        }
        return $serverParams['REMOTE_ADDR'];
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