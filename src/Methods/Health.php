<?php

namespace App\Methods;

use App\Core\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class Health
{
    public function __invoke(ServerRequestInterface $request): Response
    {
        return JsonResponse::ok(['result'=>true, 'message'=>'application is up and running']);
    }

}