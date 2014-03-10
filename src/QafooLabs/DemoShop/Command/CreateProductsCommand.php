<?php

namespace QafooLabs\DemoShop\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use QafooLabs\DemoShop\Model\ShopProduct;

class CreateProductsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('demoshop:create-products')
            ->setDescription('Generate a number of random local products')
            ->addArgument('shop', InputArgument::REQUIRED, 'Shop name to generate products for.')
            ->addOption('num-products', null, InputOption::VALUE_REQUIRED, 'Number of products to generate.', 20)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shop = $input->getArgument('shop');
        $bepadoFactory = $this->getHelper('bepado')->getBepadoFactory();
        $gateway = $bepadoFactory->getShopProductGateway($shop);

        $sdk = $bepadoFactory->getSDK($shop);
        $categories = $sdk->getCategories();

        $num = $input->getOption('num-products');

        $things = array('Phaser', 'Stick', 'Book', 'Chair', 'Shoes', 'Bottle', 'Table', 'CD', 'Wine', 'Tea', 'Hat', 'Robe', 'Plant', 'Telephone', 'Candle', 'Mug', 'Lamp', 'Marbles');
        $colors = array('Blue', 'Red', 'Yellow', 'Green', 'Pink', 'Brown', 'Black', 'White', 'Orange');
        $sizes = array('XL', 'M', 'Big', 'Small', 'For Kids', '20cm', '30cm', '40cm', '100cm');
        $companies = array('Acme Coorp', 'CHOAM', 'Sirius Cybernetics Corp.', 'MomCorp', 'Very Big Corp. of America', 'Wayne Enterprises', 'Duff', 'Monsters Inc.');

        for ($i = 0; $i < $num; $i++) {
            $title = $colors[array_rand($colors)] . " " . $things[array_rand($things)] . " " . $sizes[array_rand($sizes)];
            $shopProduct = new ShopProduct(array(
                'title' => $title,
                'shortDescription' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'longDescription' => 'Integer ipsum dolor, ornare eget velit vestibulum, dapibus porttitor lorem. Interdum et malesuada fames ac ante ipsum primis in faucibus. Phasellus ut faucibus arcu, sit amet viverra tortor. Vestibulum scelerisque viverra nunc, et consequat urna ultricies at. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Morbi eget eleifend augue. Quisque porta orci nec auctor imperdiet. Integer et lectus sit amet eros pellentesque ornare quis sit amet nisi. Morbi in felis malesuada, consequat dolor a, ornare felis.',
                'vendor' => $companies[array_rand($companies)],
                'vat' => 0.19,
                'price' => $price = (rand(0, 10000) * 0.01),
                'purchasePrice' => $price / (1 + rand(0, 10) / 100),
                'currency' => 'EUR',
                'deliveryDate' => null,
                'availability' => rand(0, 100),
                'images' => array('http://placehold.it/' . rand(100, 400) . 'x' . rand(100, 400)),
                'category' => array_rand($categories),
                'deliveryWorkDays' => rand(1, 10),
            ));

            $id = $gateway->store($shopProduct);

            $gateway->exportShopProductToBepado($id);
            $sdk->recordInsert($id);
        }
    }
}
