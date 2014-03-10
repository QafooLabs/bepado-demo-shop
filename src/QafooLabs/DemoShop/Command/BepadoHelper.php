<?php

namespace QafooLabs\DemoShop\Command;

use Symfony\Component\Console\Helper\Helper;
use QafooLabs\DemoShop\Bepado\BepadoFactory;

class BepadoHelper extends Helper
{
    /**
     * @var \QafooLabs\DemoShop\Bepado\BepadoFactory
     */
    private $factory;

    public function __construct(BepadoFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return \QafooLabs\DemoShop\Bepado\BepadoFactory
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
