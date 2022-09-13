<?php declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\JwtHelper;

final class Guard
{
    protected JwtHelper $jwtHelper;

    public function __construct(JwtHelper $jwtHelper)
    {
        $this->jwtHelper = $jwtHelper;
    }

    public function protect(callable $middleware): ProtectedRoute
    {
        return new ProtectedRoute($middleware, $this->jwtHelper);
    }

}
