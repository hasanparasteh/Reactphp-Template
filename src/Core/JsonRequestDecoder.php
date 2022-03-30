<?php

declare(strict_types=1);

namespace App\Core;

use Psr\Http\Message\ServerRequestInterface;

final class JsonRequestDecoder
{
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        if (in_array($request->getHeaderLine('content-type'), ['application/json;charset=utf-8', 'application/json'])) {
            $request = $request->withParsedBody(
                json_decode($request->getBody()->getContents(), true)
            );
        }

        return $next($request);
    }
}
