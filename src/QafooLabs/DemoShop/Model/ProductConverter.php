<?php

namespace QafooLabs\DemoShop\Model;

use DateTime;
use Bepado\SDK\Struct\Product;

/**
 * Converter between your Shop Product and Bepado Product datastructures.
 *
 * You will always need to work with {@link \Bepado\SDK\Struct\Product} objects
 * from the SDK APIs. You need to convert them into your own products or back.
 */
class ProductConverter
{
    public function convertToSDK(ShopProduct $shopProduct)
    {
        $data = (array)$shopProduct;

        // Example conversion, delivery date is a DateTime in our shops model
        // but a unix timestamp in Bepado.
        if ($data['deliveryDate'] instanceof DateTime) {
            $data['deliveryDate'] = $data['deliveryDate']->format('U');
        }

        // Category Mapping between bepado categories and your own is most
        // important. We use the same category data here for simplicty.
        $data['categories'] = array($data['category']);

        unset(
            $data['category'],
            $data['id']
        );

        return new Product($data);
    }

    public function convertToShop(Product $sdkProduct)
    {
        $data = (array)$sdkProduct;

        if ($data['deliveryDate']) {
            $data['deliveryDate'] = new \DateTime('@' . $data['deliveryDate']);
        }

        if (count($data['categories'])) {
            $data['category'] = $data['categories'][0];
        }

        unset(
            $data['shopId'],
            $data['sourceId'],
            $data['tags'],
            $data['relevance'],
            $data['categories']
        );

        return new ShopProduct($data);
    }
}
