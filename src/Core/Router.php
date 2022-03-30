<?php declare(strict_types=1);

namespace App\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

final class Router
{
    private Dispatcher $dispatcher;

    public function __construct(RouteCollector $routes)
    {
        $this->dispatcher = new Dispatcher\GroupCountBased($routes->getData());
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(), $request->getUri()->getPath()
        );

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return JsonResponse::notFound("Route Not Found");
            case Dispatcher::METHOD_NOT_ALLOWED:
                return JsonResponse::notAllowed("Method not allowed");
            case Dispatcher::FOUND:
                return $routeInfo[1]($request, ...array_values($routeInfo[2]));

        }
        throw new LogicException('Something went wrong with routing');
    }
}
