<?php

namespace QafooLabs\DemoShop\EventListener;

use Symfony\Component\HttpKernel\EventListener\SessionListener;
use Symfony\Component\HttpFoundation\Session\Session;

class SymfonySessionListener extends SessionListener
{
    protected function getSession()
    {
        return new Session();
    }
}
