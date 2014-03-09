<?php

namespace QafooLabs\DummyShop\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use QafooLabs\DummyShop\Bepado\BepadoFactory;

class SdkController
{
    /**
     * @var \QafooLabs\DummyShop\Bepado\BepadoFactory
     */
    private $factory;

    public function __Construct(BepadoFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handleAction(Request $request, $shop)
    {
        return new Response(
            $this->factory->getSDK($shop)->handle(
                $request->getContent(), // file_get_contents('php://input');
                $request->server->all() // $_SERVER
            ),
            200,
            array('Content-Type' => 'application/xml')
        );
    }
}
