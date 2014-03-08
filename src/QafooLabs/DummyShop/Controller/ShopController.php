<?php

namespace QafooLabs\DummyShop\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopController
{
    public function catalogAction(Request $request)
    {
        return new Response('Hello World', 200);
    }
}
