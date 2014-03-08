<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use QafooLabs\DummyShop;

$request = Request::createFromGlobals();
$routes = new RouteCollection();

$shopController = new DummyShop\Controller\ShopController();

$routes->add('catalog', new Route('/', array(
    '_controller' => array($shopController, 'catalogAction'),
)));

$context = new Routing\RequestContext();
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);
$resolver = new HttpKernel\Controller\ControllerResolver();

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));

$errorHandler = array(new DummyShop\Controller\ErrorController, 'exceptionAction');
$dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));
$dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));

$framework = new DummyShop\Framework($dispatcher, $resolver);

$response = $framework->handle($request);
$response->send();
