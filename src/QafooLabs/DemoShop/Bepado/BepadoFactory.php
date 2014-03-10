<?php

namespace QafooLabs\DemoShop\Bepado;

use QafooLabs\DemoShop\Model\ProductConverter;
use QafooLabs\DemoShop\Model\ShopProductGateway;
use Bepado\SDK\SDKBuilder;
use Doctrine\DBAL\DriverManager;

class BepadoFactory
{
    private $config;
    private $sdks = array();
    private $conns = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function initEnvironment()
    {
        putenv("_SOCIALNETWORK_HOST=sn." . $this->config['bepado']['host']);
        putenv("_TRANSACTION_HOST=transaction." . $this->config['bepado']['host']);
        putenv("_SEARCH_HOST=search." . $this->config['bepado']['host']);
    }

    /**
     * @return string[]
     */
    public function getShops()
    {
        return array_keys($this->config['shops']);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($shop)
    {
        $this->assertShopExists($shop);

        if (isset($this->conns[$shop])) {
            return $this->conns[$shop];
        }

        $params = $this->config['mysql'];
        $params['driver'] = 'pdo_mysql';
        $params['dbname'] = 'bepado_shopdummy_' . $shop;

        return $this->conns[$shop] = DriverManager::getConnection($params);
    }

    public function getShopProductGateway($shop)
    {
        return new ShopProductGateway($this->getConnection($shop));
    }

    /**
     * @return \Bepado\SDK\SDK
     */
    public function getSDK($shop)
    {
        if (isset($this->sdks[$shop])) {
            return $this->sdks[$shop];
        }

        $this->initEnvironment();
        $this->assertShopExists($shop);

        $conn = $this->getConnection($shop);

        $converter = new ProductConverter($this->config['dummy']['host'] . "/" . $shop);
        $gateway = new ShopProductGateway($conn);

        $builder = new \Bepado\SDK\SDKBuilder();
        $builder
            ->setApiKey($this->config['shops'][$shop]['apiKey'])
            ->setApiEndpointUrl($this->config['dummy']['host'] . "/" . $shop . "/bepado")
            ->configurePDOGateway($conn->getWrappedConnection())
            ->setProductToShop(new SimpleProductToShop($conn, $converter, $gateway))
            ->setProductFromShop(new SimpleProductFromShop($converter, $gateway))
            ->setPluginSoftwareVersion('QafooLabs ExampleShop')
        ;

        $this->sdks[$shop] = $builder->build();

        // This should be done in your plugin, when the user enters his bepado api key
        $this->sdks[$shop]->verifyKey($this->config['shops'][$shop]['apiKey']);

        return $this->sdks[$shop];
    }

    /**
     * @return \QafooLabs\DemoShop\Bepado\Faker
     */
    public function getFaker($shop)
    {
        $this->assertShopExists($shop);

        return new Faker(
            $this->config['dummy']['host'] . '/' . $shop . '/bepado',
            $this->config['shops'][$shop]['apiKey']
        );
    }

    private function assertShopExists($shop)
    {
        if (!isset($this->config['shops'][$shop])) {
            throw new \RuntimeException(sprintf('No shop "%s" exists.', $shop));
        }
    }
}
