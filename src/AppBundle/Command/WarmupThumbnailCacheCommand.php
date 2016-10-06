<?php

namespace AppBundle\Command;

use AppBundle\Controller\IIIFController;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
            ->setDescription('generates images for iiif');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('solarium.client');
        $query = $client->createSelect()->addSort('dateindexed', 'desc')->setRows(10000);
        $resultset = $client->select($query);

        $controller = new IIIFController();
        $controller->setContainer($this->getContainer());

        foreach ($resultset as $document) {
            if (isset($document->presentation_url)) {
                foreach ($document->presentation_url as $url) {
                    $image = str_replace('http://gdz.sub.uni-goettingen.de/tiff/', '', $url);
                    $image = str_replace('.tif', '', $image);
                    $image = str_replace('/', ':', $image);
                    $output->write($image);
                    $start = microtime();
                    try {
                        $controller->indexAction($image, 'full', $this->getContainer()->getParameter('thumbnail_size'), 0, 'default', 'jpg');
                    } catch (\Exception $e) {
                        $this->getContainer()->get('logger')->log(Logger::ERROR, $e->getMessage());
                    }
                    $end = microtime();
                    $output->write(' in '.($end - $start), true);
                }
            }
        }
    }
}
