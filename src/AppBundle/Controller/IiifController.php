<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IiifController extends Controller
{
    /**
     * @Route("/image")
     */
    public function indexAction()
    {
        $fs = new Filesystem();

        $client = $this->get('guzzle.client.tiff');
        $imagine = $this->get('liip_imagine');

        $downloadedFile = $this->get('kernel')->getRootDir().'/Resources/image/00000001.tiff';

        $client->get('PPN660924609/00000009.tif', ['sink' => $downloadedFile]);

        $image = $imagine->open($downloadedFile);

        try {
            $fs->remove($downloadedFile);
        } catch (IOException $e) {
            $this->get('logger')->addError(sprintf('File %s could no be deleted.', $downloadedFile));
        }

        return new BinaryFileResponse($image->show('jpg'));
    }
}
