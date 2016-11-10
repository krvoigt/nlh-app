<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

class OaiController extends Controller
{
    /**
     * @Route("/oai2/")
     */
    public function indexAction()
    {
        $this->get('oai_service')->start();
        $response = new Response();
        $response->setContent($this->get('oai_service')->oai->saveXML());
        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setStatusCode(Response::HTTP_OK);

        $this->deleteExpiredResumptionTokens();

        return $response;
    }

    protected function deleteExpiredResumptionTokens()
    {
        $time = time() - 259200;
        $fs = new Filesystem();
        $directory = $this->getParameter('oai_temp_directory');
        $fs->mkdir($directory);

        $finder = new Finder();
        $finder->files()->in($directory);
        foreach ($finder as $file) {
            if ($fs->exists($file) && substr($file->getFilename(), 0, 4) == 'oai_') {
                if ($file->getMTime() < $time) {
                    $fs->remove($file);
                }
            }
        }
    }
}
