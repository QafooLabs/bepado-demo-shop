<?php

namespace QafooLabs\DemoShop\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FakeProductCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('demoshop:fake-product')
            ->setDescription('Fake a test product send from bepado')
            ->addArgument('shop', InputArgument::REQUIRED, 'Shop name to fake a product for.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bepadoFactory = $this->getHelper('bepado')->getBepadoFactory();
        $faker = $bepadoFactory->getFaker($input->getArgument('shop'));
        $response = $faker->fakeProduct();

        $output->writeln($response);
    }
}
