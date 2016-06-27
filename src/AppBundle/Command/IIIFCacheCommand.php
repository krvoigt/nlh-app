<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class IIIFCacheCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('iiif:cache')
            ->setDescription('Clears IIIF image cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();

        $cacheDirectory = $this->getContainer()->getParameter('image_cache');

        if ($fileSystem->exists($cacheDirectory)) {
            $fileSystem->remove($cacheDirectory);
        }
        $fileSystem->mkdir($cacheDirectory);
        $fileSystem->touch($cacheDirectory.'/.gitkeep');
        $output->writeln($this->getContainer()->get('translator')->trans('Cleared IIIF image cache'));
    }
}
