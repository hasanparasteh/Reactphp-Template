<?php

declare(strict_types=1);

namespace App\Core;

use React\Http\Message\Response;

final class JsonResponse
{
    private static function response(int $statusCode, $data = null): Response
    {
        $body = $data ? json_encode($data) : '';

        return new Response($statusCode, ['Content-Type' => 'application/json'], $body);
    }

    public static function ok($data): Response
    {
        return self::response(200, $data);
    }

    public static function internalServerError($error): Response
    {
        return self::response(500, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function notFound($error): Response
    {
        return self::response(404, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function badRequest($error): Response
    {
        return self::response(400, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function limitExceed(): Response
    {
        return self::response(429, ['result' => false, 'error' => self::errorFormatter("To Many Requests")]);
    }

    public static function alreadyDone($error): Response
    {
        return self::response(409, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function unProcessable($error): Response
    {
        return self::response(422, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function unauthorized($error): Response
    {
        return self::response(401, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function notAllowed($error): Response
    {
        return self::response(405, ['result' => false, 'error' => self::errorFormatter($error)]);
    }

    public static function permanentRedirect($link): Response
    {
        return new Response(301, ['Location' => $link]);
    }

    private static function errorFormatter($error): ?string
    {
        if (is_null($error)) return null;

        $error = str_replace("   ", " ", $error);
        $error = str_replace("  ", " ", $error);
        $error = str_replace(".", "", $error);
        $error = str_replace("!", "", $error);
        $error = str_replace("-", "", $error);
        $error = str_replace(" ", "_", $error);
        return strtoupper($error);
    }
}
