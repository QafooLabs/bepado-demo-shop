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

$bepadoFactory = new DummyShop\Bepado\BepadoFactory(
    json_decode(file_get_contents(__DIR__ . '/../shops.json'), true)
);

$loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
$twig = new Twig_Environment($loader, array());

$shopController = new DummyShop\Controller\ShopController($bepadoFactory, $twig);
$sdkController = new DummyShop\Controller\SdkController($bepadoFactory);

$routes = new RouteCollection();
$routes->add('catalog', new Route('/{shop}', array(
    '_controller' => array($shopController, 'catalogAction'),
)));
$routes->add('basket_add', new Route(
    '/{shop}/basket-add',
    array('_controller' => array($shopController, 'basketAddAction'))
));
$routes->add('checkout', new Route(
    '/{shop}/checkout',
    array('_controller' => array($shopController, 'checkoutAction'))
));
$routes->add('basket_show', new Route('/{shop}/basket', array(
    '_controller' => array($shopController, 'basketAction'),
)));
$routes->add('bepado_api', new Route('/{shop}/bepado', array(
    '_controller' => array($sdkController, 'handleAction'),
)));

$context = new Routing\RequestContext();
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);
$resolver = new HttpKernel\Controller\ControllerResolver();

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));

$errorHandler = array(new DummyShop\Controller\ErrorController, 'exceptionAction');
$dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));
$dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));
$dispatcher->addSubscriber(new DummyShop\EventListener\SymfonySessionListener());

$framework = new DummyShop\Framework($dispatcher, $resolver);

$request = Request::createFromGlobals();
$response = $framework->handle($request);
$response->send();
