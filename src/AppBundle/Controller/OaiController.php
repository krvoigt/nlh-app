<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

    private function deleteExpiredResumptionTokens()
    {
        $time = time() - 259200;
        $filesystem = $this->get('oneup_flysystem.cloud_filesystem');
        $contents = $filesystem->listContents('oai-gdz/');
        foreach ($contents as $object) {
            if ($object['mtime'] < $time) {
                $filesystem->delete($object);
            }
        }
    }
}
