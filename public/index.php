<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Slim\Handlers\Strategies\RequestResponseArgs;
use App\Middleware\AddJsonResponseHeader;
use App\Controllers\GridIndex;
use App\Controllers\Grids;
use App\Middleware\GetGrid;
use Slim\Routing\RouteCollectorProxy;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

$dotenv->load();

$builder = new ContainerBuilder;

$container = $builder->addDefinitions(APP_ROOT . '/config/definitions.php')
                     ->build();

AppFactory::setContainer($container);

$app = AppFactory::create();

$collector = $app->getRouteCollector();

$collector->setDefaultInvocationStrategy(new RequestResponseArgs);

$app->addBodyParsingMiddleware();

$error_middleware = $app->addErrorMiddleware(true, true, true);

$error_handler = $error_middleware->getDefaultErrorHandler();

$error_handler->forceContentType('application/json');

$app->add(new AddJsonResponseHeader);

$app->group('/api', function (RouteCollectorProxy $group) {

    $group->get('/grids', GridIndex::class);

    $group->post('/grids', [Grids::class, 'create']);

    $group->group('', function (RouteCollectorProxy $group) {

        $group->get('/grids/{id:[0-9]+}', Grids::class . ':show');

        $group->patch('/grids/{id:[0-9]+}', Grids::class . ':update');

        $group->delete('/grids/{id:[0-9]+}', Grids::class . ':delete');

    })->add(GetGrid::class);

});

$app->run();