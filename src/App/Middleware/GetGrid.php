<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use App\Repositories\GridRepository;
use Slim\Exception\HttpNotFoundException;

class GetGrid
{
    public function __construct(private GridRepository $repository)
    {
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $context = RouteContext::fromRequest($request);

        $route = $context->getRoute();

        $id = $route->getArgument('id');

        $grid = $this->repository->getById((int) $id);
    
        if ($grid === false) {
    
            throw new HttpNotFoundException($request,message: 'grid not found');
    
        }

        $request = $request->withAttribute('grid', $grid);

        return $handler->handle($request);
    }
}