<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\JsonResponse;
use App\Helpers\GlobalHelper;
use Psr\Http\Message\ServerRequestInterface;
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use React\Http\Message\Response;

final class IPLimiter
{
    protected RedisRateLimiter $rateLimiter;

    public function __construct(RedisRateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $clientIp = GlobalHelper::parseClientIp($request);
        if (!$clientIp['result'])
            return JsonResponse::internalServerError("Cant Identify Client");

        if (strlen($clientIp['ip']) > 2 &&
            !in_array($clientIp['ip'], GlobalHelper::$whiteListIps, true)
        ) if ($this->rateLimiter
                ->limitSilently($clientIp['ip'], Rate::perMinute(50))
                ->getRemainingAttempts() == 0
        ) return JsonResponse::limitExceed();

        return $next($request);
    }

}