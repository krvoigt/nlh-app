<?php

namespace AppBundle\Command;

use AppBundle\Controller\IIIFController;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WarmupThumbnailCacheCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:warmup_thumbnail_cache')
            ->setDescription('generates images for iiif')
            ->addOption('direction', null, InputOption::VALUE_OPTIONAL, 'Sorting direction (asc or desc)', 'desc')
            ->addOption('rows', null, InputOption::VALUE_OPTIONAL, 'Number of rows', 500);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('solarium.client');
        $query = $client->createSelect()->addSort('date_indexed', $input->getOption('direction'))->setRows($input->getOption('rows'));
        $resultset = $client->select($query);

        $controller = new IIIFController();
        $controller->setContainer($this->getContainer());

        foreach ($resultset as $document) {
            if (isset($document->nlh_id)) {
                foreach ($document->nlh_id as $image) {
                    $output->write($image);
                    $start = microtime(true);
                    try {
                        $controller->indexAction($image, 'full', $this->getContainer()->getParameter('thumbnail_size'), '0', 'default', 'jpg');
                    } catch (\Exception $e) {
                        $this->getContainer()->get('logger')->log(Logger::ERROR, $e->getMessage());
                    }
                    $end = microtime(true);
                    $output->write(' in '.number_format($end - $start, 2).'s', true);
                }
            }
        }
    }
}
