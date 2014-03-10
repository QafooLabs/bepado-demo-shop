<?php

namespace QafooLabs\DemoShop\Bepado;

use QafooLabs\DemoShop\Model\ProductConverter;
use QafooLabs\DemoShop\Model\ShopProductGateway;
use Bepado\SDK\ProductFromShop;
use Bepado\SDK\Struct;

class SimpleProductFromShop implements ProductFromShop
{
    /**
     * @var \QafooLabs\DemoShop\Bepado\Model\ProductConverter
     */
    private $converter;

    /**
     * @var \QafooLabs\DemoShop\Bepado\Model\ShopProductGateway
     */
    private $gateway;

    public function __construct(ProductConverter $converter, ShopProductGateway $gateway)
    {
        $this->converter = $converter;
        $this->gateway = $gateway;
    }

    /**
     * Get product data
     *
     * Get product data for all the product IDs specified in the given string
     * array.
     *
     * @param string[] $ids
     * @return Struct\Product[]
     */
    public function getProducts(array $ids)
    {
        $shopProducts = $this->gateway->findProductsById($ids);
        $sdkProducts = array();

        foreach ($shopProducts as $shopProduct) {
            $sdkProducts[] = $this->converter->convertToSDK($shopProduct);
        }

        return $sdkProducts;
    }

    /**
     * Get all IDs of all exported products
     *
     * @return string[]
     */
    public function getExportedProductIDs()
    {
        // Not necessary for a shop that uses SDK#recordUpdate, recordInsert and recordDelete
        return array();
    }

    /**
     * Reserve a product in shop for purchase
     *
     * @param Struct\Order $order
     * @return void
     * @throws \Exception Abort reservation by throwing an exception here.
     */
    public function reserve(Struct\Order $order)
    {
        // If your shop system supports reservations, use this method
        // to create the order as a reservation. It is not necessary
        // to implement reservations, only a convenience.
    }

    /**
     * Buy products mentioned in order
     *
     * Should return the internal order ID.
     *
     * @param Struct\Order $order
     * @return string
     *
     * @throws \Exception Abort buy by throwing an exception,
     *                    but only in very important cases.
     *                    Do validation in {@see reserve} instead.
     */
    public function buy(Struct\Order $order)
    {
    }
}
