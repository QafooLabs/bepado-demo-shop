<?php

namespace QafooLabs\ExampleShop\Command;

use Symfony\Component\Console\Helper\Helper;
use QafooLabs\ExampleShop\Bepado\BepadoFactory;

class BepadoHelper extends Helper
{
    /**
     * @var \QafooLabs\ExampleShop\Bepado\BepadoFactory
     */
    private $factory;

    public function __construct(BepadoFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return \QafooLabs\ExampleShop\Bepado\BepadoFactory
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
