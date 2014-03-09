<?php

namespace QafooLabs\ExampleShop\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Contains the storage logic for this ExampleShop, including logic to handle bepado.
 *
 * bepado products should be saved in the same datastructure than your normal
 * products.
 *
 * See the docblocks and inline comments for implementation details.
 */
class ShopProductGateway
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var array
     */
    private $fields = array();

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        $this->init();
    }

    public function store(ShopProduct $product)
    {
        $row = array();

        foreach ($this->fields as $field => $metadata) {
            $row[$metadata['column']] = Type::getType($metadata['type'])->convertToDatabaseValue(
                $product->$field, $this->conn->getDatabasePlatform()
            );
        }

        if ($row['p_id'] === null) {
            $this->conn->insert('shop_products', $row);
            $product->id = $this->conn->lastInsertId();
        } else {
            $this->conn->update('shop_products', $row, array('p_id' => $row['p_id']));
        }

        return $product->id;
    }

    public function delete($shopProductId)
    {
        return $this->conn->delete('shop_products', array('p_id' => $shopProductId));
    }

    public function findProductsById(array $ids)
    {
        $sql = 'SELECT * FROM shop_products WHERE p_id IN (?)';
        $rows = $this->conn->fetchAll($sql, array($ids), array(Connection::PARAM_INT_ARRAY));

        return $this->createProducts($rows);
    }

    public function findProductsByCategory($category)
    {
        $sql = 'SELECT * FROM shop_products WHERE p_category = ?';
        $rows = $this->conn->fetchAll($sql, array($category), array());

        return $this->createProducts($rows);
    }

    private function createProducts($rows)
    {
        $products = array();

        foreach ($rows as $row) {
            $data = array();

            foreach ($this->fields as $field => $metadata) {
                $data[$field] = Type::getType($metadata['type'])->convertToPhpValue(
                    $row[$metadata['column']], $this->conn->getDatabasePlatform()
                );
            }

            $products[] = new ShopProduct($data);
        }

        return $products;
    }

    /**
     * @return array<string,int>
     */
    public function findCategories()
    {
        $sql = 'SELECT p_category, count(*) as products FROM shop_products GROUP BY p_category';
        $rows = $this->conn->fetchAll($sql);

        $categories = array();
        foreach ($rows as $row) {
            $categories[$row['p_category']] = $row['products'];
        }

        return $categories;
    }

    /**
     * Store Bepado Attributes of a Product.
     *
     * Some attributes of Bepado Products cannot probably not be saved on your
     * own products, because there are no matching fields for them. This includes
     * the bepado shopId and sourceId of the product. It could be more fields
     * depending on your shop system.
     *
     * You have to save this information on a new table then to have it available.
     */
    public function storeBepadoAttributes($productId, $shopId, $sourceId)
    {
        $sql = 'INSERT INTO shop_bepado_attributes (p_id, sba_bepado_shop_id, sba_bepado_source_id)
                     VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE sba_bepado_shop_id = VALUES(sba_bepado_shop_id),
                                        sba_bepado_source_id = VALUES(sba_bepado_source_id)';
        $this->conn->executeUpdate($sql, array($productId, $shopId, $sourceId));
    }

    /**
     * The bepado attributes of your ShopProducts are needed when you convert a ShopProduct
     * into an SDK product during a remote bepado transaction.
     */
    public function getBepadoShopProductId($shopId, $sourceId)
    {
        $sql = 'SELECT p_id FROM shop_bepado_attributes WHERE sba_bepado_shop_id = ? AND sba_bepado_source_id = ?';

        return $this->conn->fetchColumn($sql, array($shopId, $sourceId));
    }

    public function getBepadoAttributes($productId)
    {
        $sql = 'SELECT sba_bepado_shop_id as shopId, sba_bepado_source_id as sourceId
                  FROM shop_bepado_attributes
                 WHERE p_id = ?';
        return $this->conn->fetchAssoc($sql, array($productId));
    }

    /**
     * Check if product is a bepado product.
     */
    public function isBepadoProduct($productId)
    {
        $sql = 'SELECT p_id FROM shop_bepado_attributes WHERE p_id = ?';
        return $this->conn->fetchColumn($sql, array($productId)) > 0;
    }

    /**
     * Explicitly mark one of your shop products as exported to bepado.
     *
     * You probably don't want to export your whole product catalogue to bepado.
     * That is why you should introduce an explicit way to mark products as exported
     * to bepado. It is **very** important that you disallow exporting already
     * imported bepado products.
     */
    public function exportShopProductToBepado($productId)
    {
        // You have to prevent that a bepado product is exported as your own product to bepado again.
        if ($this->isBepadoProduct()) {
            throw new \RuntimeException("You have to prevent that a bepado product is exported as your own product to bepado again.");
        }

        $sql = "INSERT IGNORE INTO shop_bepado_exported (p_id) VALUES (?)";
        $this->conn->executeUpdate($sql, array($productId));
    }

    /**
     * Remove explicit bepado exported status
     */
    public function removeBepadoExportStatus($productId)
    {
        $this->conn->delete('shop_bepado_exported', array('p_id' => $productId));
    }

    /**
     * Create SQL Schema metadata.
     *
     * Not important for understanding Bepado Workflows.
     * Used to create the sql schema of the shop.
     */
    public function getSchema()
    {
        $schema = new Schema();

        $table = $schema->createTable('shop_products');

        foreach ($this->fields as $metadata) {
            $table->addColumn($metadata['column'], $metadata['type'], array(
                'notnull' => $metadata['notnull'],
                'autoincrement' => ($metadata['column'] === 'p_id')
            ));
        }

        $table->setPrimaryKey(array('p_id'));
        $table->addIndex(array('p_category'));

        $table = $schema->createTable('shop_bepado_attributes');
        $table->addColumn('p_id', 'integer');
        $table->addColumn('sba_bepado_shop_id', 'string');
        $table->addColumn('sba_bepado_source_id', 'string');
        $table->setPrimaryKey(array('p_id'));
        $table->addIndex(array('sba_bepado_shop_id', 'sba_bepado_source_id'));

        $table = $schema->createTable('shop_bepado_exported');
        $table->addColumn('p_id', 'integer');
        $table->setPrimaryKey(array('p_id'));

        return $schema;
    }

    private function init()
    {
        $this
            ->addField('id', 'p_id', 'integer')
            ->addField('title', 'p_title', 'string')
            ->addField('ean', 'p_ean', 'string', false)
            ->addField('shortDescription', 'p_short_description', 'string')
            ->addField('longDescription', 'p_long_description', 'string')
            ->addField('vendor', 'p_vendor', 'string')
            ->addField('vat', 'p_vat', 'float')
            ->addField('price', 'p_price', 'float')
            ->addField('purchasePrice', 'p_purchase_price', 'float')
            ->addField('currency', 'p_currency', 'string')
            ->addField('deliveryDate', 'p_delivery_date', 'datetime',false)
            ->addField('availability', 'p_availability', 'integer')
            ->addField('images', 'p_images', 'simple_array')
            ->addField('category', 'p_category', 'string')
            ->addField('attributes', 'p_attributes', 'json_array')
            ->addField('deliveryWorkDays', 'p_delivery_workdays', 'integer', false)
        ;
    }

    private function addField($field, $column, $type, $notNull = true)
    {
        $this->fields[$field] = array('column' => $column, 'type' => $type, 'notnull' => $notNull);
        return $this;
    }
}
