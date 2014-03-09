<?php

namespace QafooLabs\DummyShop\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use QafooLabs\DummyShop\Model\ShopProductGateway;

class CreateDatabaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('shopdummy:create-database')
            ->setDescription('Create all databases')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bepadoFactory = $this->getHelper('bepado')->getBepadoFactory();

        foreach ($bepadoFactory->getShops() as $shop) {
            $output->write(sprintf('Attempt to create database for shop "%s": ', $shop));

            $this->createDatabase($shop, $bepadoFactory);

            $output->writeln('<info>Done</info>');
        }
    }

    private function createDatabase($shop, $bepadoFactory)
    {
        $connection = $bepadoFactory->getConnection($shop);

        $schemaDir = __DIR__ . '/../../../schema';
        $sqlFiles = array_filter(
            scandir($schemaDir),
            function ($file) { return substr($file, -4) === '.sql'; }
        );

        sort($sqlFiles);
        foreach ($sqlFiles as $sqlFile) {
            $sql = file_get_contents($schemaDir . '/' . $sqlFile);
            $connection->exec($sql);
        }
    }
}