<?php

declare(strict_types=1);

namespace App\Core;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use function React\Promise\resolve;
use Throwable;


final class ErrorHandler
{
    public function __invoke(ServerRequestInterface $request, callable $next): RejectedPromise|PromiseInterface|FulfilledPromise|Promise|Response
    {
        try {
            return resolve($next($request))
                ->then(
                    function (Response $response) use ($request) {
                        return $response;
                    },
                    function (Throwable $error) use ($request) {
                        return JsonResponse::internalServerError($error->getMessage());
                    }
                );
        } catch (Throwable $error) {
            return JsonResponse::internalServerError($error->getMessage());
        }
    }
}
