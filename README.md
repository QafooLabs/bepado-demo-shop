# Bepado SDK Example Shop

This repository contains an example shop with bepado SDK API implemented. It
is integrated into a very simple shop system built with Symfony components.

## Where to look?

1. Take a look at the
   ``src/QafooLabs/DummyShop/Bepado/SimpleProductFromShop.php`` and
   ``SimpleProductToShop.php`` files.  For an implementation of the two bepado
   SDK interfaces that you have to do in your own plugin.
2. The folder ``src/QafooLabs/DummyShop/Model`` contains models and services
   related to the shop system.  You will need to take a look at the
   ``BasketService.php``, ``ProductConverter.php`` and
   ``ShopProductGateway.php``.  They contain a lot of the logic and storage
   related code that is necessary to work with the bepado SDK.
3. The controller ``src/QafooLabs/DummyShop/Controller/SdkController.php`` is
   an example of how to setup the SDK RPC endpoint.
4. The controller ``src/QafooLabs/DummyShop/Controller/ShopController.php``
   contains the category listing, basket listing and checkout with calls to the
   relevant services invoking bepado SDK code.

## Installation

You can install this project via Composer:

    composer create-project bepado/sdk-example-shop

Then you need to copy the "shops.dist.json" to "shops.json" and adjust the
data. You need to get in contact with `bepado@shopware.com` to receive an
account and api key on the Test system.

Create a database ``bepado_dummyshop_$shop`` where $shop is the key in the shops
array of your ``shops.json``. Call ``php src/bin/dummyshop dummyshop:create-database``
to create the schema for the database.

To receive some dummy data from a remote shop, call ``php src/bin/dummyshop dummyshop:fake-product``.

You can use the builtin server to run the shop:

    php -S localhost:8080 web/index.php

Note: The installation and running of this shop is not supported by us. It is
just meant to be a helpful resource when implementing your own plugin.
