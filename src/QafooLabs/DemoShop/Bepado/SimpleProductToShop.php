<?php

namespace QafooLabs\DemoShop\Bepado;

use QafooLabs\DemoShop\Model\ProductConverter;
use QafooLabs\DemoShop\Model\ShopProductGateway;
use Bepado\SDK\ProductToShop;
use Bepado\SDK\Struct;

use Doctrine\DBAL\Connection;

class SimpleProductToShop implements ProductToShop
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var \QafooLabs\DemoShop\Bepado\Model\ProductConverter
     */
    private $converter;

    /**
     * @var \QafooLabs\DemoShop\Bepado\Model\ShopProductGateway
     */
    private $gateway;

    public function __construct(Connection $conn, ProductConverter $converter, ShopProductGateway $gateway)
    {
        $this->conn = $conn;
        $this->converter = $converter;
        $this->gateway = $gateway;
    }

    /**
     * Import or update given product
     *
     * Store product in your shop database as an external product. The
     * associated sourceId
     *
     * @param Struct\Product $product
     */
    public function insertOrUpdate(Struct\Product $product)
    {
        $localProductId = $this->gateway->getBepadoShopProductId(
            $product->shopId, $product->sourceId
        );

        $shopProduct = $this->converter->convertToShop($product);

        if ($localProductId) {
            $shopProduct->id = $localProductId;
        }

        $productId = $this->gateway->store($shopProduct);

        $this->gateway->storeBepadoAttributes($productId, $product->shopId, $product->sourceId);
    }

    /**
     * Delete product with given shopId and sourceId.
     *
     * Only the combination of both identifies a product uniquely. Do NOT
     * delete products just by their sourceId.
     *
     * You might receive delete requests for products, which are not available
     * in your shop. Just ignore them.
     *
     * @param string $shopId
     * @param string $sourceId
     * @return void
     */
    public function delete($shopId, $sourceId)
    {
        $shopProductId = $this->gateway->getBepadoShopProductId($shopId, $sourceId);

        if ($shopProductId) {
            $this->gateway->delete($shopProductId);
        }
    }

    /**
     * Start transaction
     *
     * Starts a transaction, which includes all insertOrUpdate and delete
     * operations, as well as the revision updates.
     *
     * @return void
     */
    public function startTransaction()
    {
        $this->conn->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * Commits the transactions, once all operations are queued.
     *
     * @return void
     */
    public function commit()
    {
        $this->conn->commit();
    }
}
