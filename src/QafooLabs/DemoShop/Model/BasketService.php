<?php

namespace QafooLabs\DemoShop\Model;

use Bepado\SDK\SDK;

class BasketService
{
    private $gateway;
    private $sdk;

    public function __construct(ShopProductGateway $gateway, SDK $sdk)
    {
        $this->gateway = $gateway;
        $this->sdk = $sdk;
    }

    public function getBasket(array $basket)
    {
        $basketProducts = $this->gateway->findProductsById(array_keys($basket));

        // 1. We check if any of the products in the basket is from bepado
        list($basketBepadoProducts, $basketLocalProducts) = $this->partitionProducts($basketProducts);

        // 2. our shopsystem creates shipments for the local order to display in basket
        $shipments = array();

        if (count($basketLocalProducts)) {
            $shipments[] = $this->createLocalShipment($basket, $basketLocalProducts);
        }

        // 3. We prepare a dummy Bepado SDK Order, to calculate the shipping costs using dummy address data.
        $totalShippingCosts = $this->calculateBepadoShippingCosts($basket, $basketBepadoProducts, array(
            'country' => 'DEU', // put in the estimated shipment country of the user here
        ));

        // 4. we create different shipments, one for each bepado shop and
        // append them to the regular shipments of local shop products.
        if ($totalShippingCosts !== false) {
            $bepadoShipments = $this->createBepadoShipments($basketBepadoProducts, $basket, $totalShippingCosts);
            $shipments = array_merge($shipments, $bepadoShipments);
        }

        // 5. we create a basket with all shipments and let our shopsystme calculate totals.
        return $this->createBasket($shipments, $basket);
    }

    public function checkout($data)
    {
        $basket = $data['items'];
        $basketProducts = $this->gateway->findProductsById(array_keys($basket));

        list($basketBepadoProducts, $basketLocalProducts) = $this->partitionProducts($basketProducts);

        $reservation = false;
        if (count($basketBepadoProducts)) {
            // 1. create a bepado order and reserve that (remote validation happens here)
            $order = $this->createBepadoOrder($data['address'], $basket, $basketBepadoProducts);
            $reservation = $this->sdk->reserveProducts($order);

            // 2. if remote reservation failed, abort checkout and show error messages to user
            if (!$reservation->success) {
                $message = '';
                foreach ($reservation->messages as $shopId => $messages) {
                    $message .= 'Shop '. $shopId . ': '. implode(", ", $messages) . "\n";
                }

                // this should be more convenient in your plugin, showing the messages in the basket.
                throw new \DomainException($message);
            }
        }

        // 3. now create our local order
        $shipments = array();
        if (count($basketLocalProducts)) {
            $shipments[] = $this->createLocalShipment($basket, $basketLocalProducts);
        }

        if (count($basketBepadoProducts)) {
            // including the bepado shipments if necessary
            $totalShippingCosts = $this->sdk->calculateShippingCosts($order);
            $shipments = array_merge($shipments, $this->createBepadoShipments($basketBepadoProducts, $basket, $totalShippingCosts));
        }

        // 4. create and save order locally here!
        // we dont do that here for simplicity of the example
        $order = new Order(array(
            // some id generated while saving the order, we dont save orders here
            'id' => time(),
            'shipments' => $shipments,
            'address' => new Address($data['address'])
        ));

        // 5. confirm reservation and buy products remotely
        // This can theoretically fail, you could use transactions to be able to rollback,
        // or do the checkout() call before saving your local order.
        if ($reservation) { // check if remote products included
            $ret = $this->sdk->checkout($reservation, $order->id);
        }

        return $order;
    }

    private function partitionProducts($basketProducts)
    {
        $basketBepadoProducts = array();
        $basketLocalProducts = array();
        foreach ($basketProducts as $product) {
            if ($this->gateway->isBepadoProduct($product->id)) {
                $basketBepadoProducts[] = $product;
            } else {
                $basketLocalProducts[] = $product;
            }
        }

        return array($basketBepadoProducts, $basketLocalProducts);
    }

    private function createBasket($shipments, $basket)
    {
        $basket = new Basket(array(
            'shipments' => $shipments,
            'isShippable' => true,
        ));

        foreach ($basket->shipments as $shipment) {
            if (!$shipment->isShippable) {
                $basket->isShippable = false;
            }

            $basket->totalGrossShippingCosts += $shipment->grossShippingCosts;
            $basket->totalNetShippingCosts += $shipment->netShippingCosts;

            foreach ($shipment->items as $item) {
                $shipment->netPrice += $item->product->price * $item->count;
                $shipment->grossPrice += ($item->product->price * (1 + $item->product->vat)) * $item->count;
                $basket->count++;
            }

            $basket->totalNetPrice += $shipment->netPrice;
            $basket->totalGrossPrice += $shipment->grossPrice;
        }

        return $basket;
    }

    private function createBepadoShipments($basketBepadoProducts, $basket, $totalShippingCosts)
    {
        $bepadoShopProducts = array();
        $bepadoShipments = array();
        foreach ($basketBepadoProducts as $shopProduct) {
            $bepadoAttributes = $this->gateway->getBepadoAttributes($shopProduct->id);
            $bepadoShopProducts[$bepadoAttributes['shopId']][] = $shopProduct;
        }

        foreach ($totalShippingCosts->shops as $shopId => $shippingCosts) {
            $bepadoShipments[] = new Shipment(array(
                // we can choose to pass on the shipping costs directly to the customer
                // or use our own shipping costs here. The decision is up to the
                // plugin implementor and may be configurable to the shop owner.
                'grossShippingCosts' => $shippingCosts->grossShippingCosts,
                'netShippingCosts' => $shippingCosts->shippingCosts,
                'isShippable' => $shippingCosts->isShippable,
                'items' => array_map(
                    function ($shopProduct) use ($basket) {
                        return new OrderItem(array(
                            'product' => $shopProduct,
                            'count' => $basket[$shopProduct->id],
                        ));
                    },
                    $bepadoShopProducts[$shopId]
                )
            ));
        }

        return $bepadoShipments;
    }

    private function calculateBepadoShippingCosts(array $basket, array $basketBepadoProducts, array $addressData)
    {
        $dummyOrder = $this->createBepadoOrder($addressData, $basket, $basketBepadoProducts);

        $bepadoProducts = array_map(function ($orderItem) { return $orderItem->product; }, $dummyOrder->orderItems);

        if (count($bepadoProducts) === 0) {
            return false;
        }

        // inside the basket view you could check if the products are still the same on the remote shops.
        // You can skip this step though, SDK#reserveProducts() does the same at a later step.
        // This only improves the usability for your users.
        #$result = $this->sdk->checkProducts($bepadoProducts);
        $result = true;

        if ($result !== true) {
            $message = '';
            foreach ($result as $shopId => $messages) {
                $message .= 'Shop '. $shopId . ': '. implode(", ", $messages) . "\n";
            }

            // this should be more convenient in your plugin, showing the messages in the basket.
            throw new \DomainException($message);
        }

        return $this->sdk->calculateShippingCosts($dummyOrder);
    }

    private function createBepadoOrder($addressData, $basket, $basketBepadoProducts)
    {
        $order = new \Bepado\SDK\Struct\Order(array(
            'deliveryAddress' => new \Bepado\SDK\Struct\Address($addressData)
        ));
        $converter = new ProductConverter();

        foreach ($basketBepadoProducts as $shopProduct) {
            // convert shop product to sdk product, append the bepado attributes
            $bepadoAttributes = $this->gateway->getBepadoAttributes($shopProduct->id);
            $sdkProduct = $converter->convertToSDK($shopProduct);
            $sdkProduct->shopId = $bepadoAttributes['shopId'];
            $sdkProduct->sourceId = $bepadoAttributes['sourceId'];

            $order->orderItems[] = new \Bepado\SDK\Struct\OrderItem(array(
                'product' => $sdkProduct,
                'count' => (int)$basket[$shopProduct->id],
            ));
        }

        return $order;
    }

    private function createLocalShipment($basket, $basketLocalProducts)
    {
        $localShipment = new Shipment(array(
            // local shipping costs are not our business here..
            'grossShippingCosts' => 3.99,
            'netShippingCosts' => 3.99 / 1.19,
        ));

        foreach ($basketLocalProducts as $product) {
            $localShipment->items[] = new OrderItem(array(
                'product' => $product,
                'count' => $basket[$product->id],
            ));
        }

        return $localShipment;
    }
}
