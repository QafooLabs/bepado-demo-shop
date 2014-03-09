<?php

namespace QafooLabs\DummyShop\Controller;

use QafooLabs\DummyShop\Model\BasketService;
use QafooLabs\DummyShop\Bepado\BepadoFactory;
use Twig_Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ShopController
{
    private $factory;
    private $twig;

    public function __construct(BepadoFactory $factory, Twig_Environment $twig)
    {
        $this->factory = $factory;
        $this->twig = $twig;
    }

    public function catalogAction(Request $request, $shop)
    {
        $gateway = $this->factory->getShopProductGateway($shop);
        $categories = $gateway->findCategories();

        $products = array();
        if ($request->query->has('category')) {
            $products = $gateway->findProductsByCategory(
                $request->query->get('category')
            );
        }

        return new Response(
            $this->twig->render('catalog.html.twig', array(
                'shop' => $shop,
                'categories' => $categories,
                'products' => $products,
            ))
        );
    }

    public function basketAction(Request $request, $shop)
    {
        $session = $request->getSession();
        $basket = $session->get('basket' . $shop);

        if (!$basket) {
            $basket = array();
        }

        $basketService = new BasketService(
            $this->factory->getShopProductGateway($shop),
            $this->factory->getSDK($shop)
        );
        $basket = $basketService->getBasket($basket);

        return new Response(
            $this->twig->render('basket.html.twig', array(
                'shop' => $shop,
                'basket' => $basket,
            ))
        );
    }

    public function checkoutAction(Request $request, $shop)
    {
        $session = $request->getSession();
        $basket = $session->get('basket' . $shop);

        if (!$basket) {
            return new RedirectResponse('/' . $shop);
        }

        $basketService = new BasketService(
            $this->factory->getShopProductGateway($shop),
            $this->factory->getSDK($shop)
        );
        $order = $basketService->checkout($request->request->all());

        $session->set('basket' . $shop, array());

        return new Response(
            $this->twig->render('checkout.html.twig', array(
                'shop' => $shop,
                'order' => $order,
            ))
        );
    }

    public function basketAddAction(Request $request, $shop)
    {
        $session = $request->getSession();
        $basket = $session->get('basket' . $shop);

        if (!$basket) {
            $basket = array();
        }

        $basket[$request->request->get('id')] = 1;
        $session->set('basket' . $shop, $basket);

        return new RedirectResponse('/' . $shop);
    }
}
