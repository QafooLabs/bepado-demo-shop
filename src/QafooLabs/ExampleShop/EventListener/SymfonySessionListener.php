<?php

namespace QafooLabs\ExampleShop\EventListener;

use Symfony\Component\HttpKernel\EventListener\SessionListener;
use Symfony\Component\HttpFoundation\Session\Session;

class SymfonySessionListener extends SessionListener
{
    protected function getSession()
    {
        return new Session();
    }
}
