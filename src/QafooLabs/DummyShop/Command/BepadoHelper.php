<?php

namespace QafooLabs\DummyShop\Command;

use Symfony\Component\Console\Helper\Helper;
use QafooLabs\DummyShop\Bepado\BepadoFactory;

class BepadoHelper extends Helper
{
    /**
     * @var \QafooLabs\DummyShop\Bepado\BepadoFactory
     */
    private $factory;

    public function __construct(BepadoFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return \QafooLabs\DummyShop\Bepado\BepadoFactory
     */
    public function getBepadoFactory()
    {
        return $this->factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bepado';
    }
}
