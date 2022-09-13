<?php

namespace App\Middleware;

use App\Core\JsonResponse;
use App\Helpers\JwtHelper;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class ProtectedRoute
{

    private $middleware;
    private JwtHelper $jwtHelper;

    public function __construct(callable $middleware, JwtHelper $jwtHelper)
    {
        $this->middleware = $middleware;
        $this->jwtHelper = $jwtHelper;
    }

    private function decodeJwtToken(ServerRequestInterface $request): Promise
    {
        $resolver = function (callable $resolve, callable $reject) use ($request) {
            $token = $this->getTokenFromHeader($request);

            if (!$token['result']) {
                $resolve(['result' => false, 'message' => "token  not exists"]);
            } else {
                if (!is_string($token['token']) || strlen($token['token']) < 20) {
                    $resolve(['result' => false, "message" => "token error"]);
                } else {
                    $decodeToken = $this->jwtHelper->toArray($token['token']);

                    if (!$decodeToken['result']) {
                        $resolve(['result' => false, 'message' => "token error"]);
                    } else {
                        $resolve(["result" => true, "value" => $decodeToken["decodeJwt"]]);
                    }
                }
            }
        };

        $canceller = function () {
            throw new Exception('Auth Promise Layer-1 Error');
        };
        return new Promise($resolver, $canceller);
    }

    private function getTokenFromHeader(ServerRequestInterface $request): array
    {
        $token = $request->getHeaderLine("Authorization");

        if (empty($token))
            return ['result' => false];

        if (!str_starts_with($token, "Bearer "))
            return ['result' => false];

        $token = substr($token, 7);
        return ['result' => true, "token" => $token];
    }

    public function __invoke(ServerRequestInterface $request, ...$args): PromiseInterface|Promise
    {
        return $this->decodeJwtToken($request)
            ->then(function ($result) use ($request, $args) {
                if (!$result['result'])
                    if (in_array($result['message'], ["REDIS_OFFLINE", "DB_OFFLINE"], true))
                        return JsonResponse::internalServerError();
                    else
                        return JsonResponse::unauthorized("Not Authorized");

                if (is_array($args) && count($args) > 0)
                    return call_user_func($this->middleware, $request, $result, ...$args);

                return call_user_func($this->middleware, $request, $result);
            });
    }
}