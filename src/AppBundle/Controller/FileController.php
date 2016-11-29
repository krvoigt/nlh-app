<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller implements IpAuthenticatedController
{
    /**
     * @Route("/download/pdf/{id}", name="_download_pdf")
     */
    public function pdfAction($id)
    {
        $fileSystem = $this->get('oneup_flysystem.nlh_filesystem');
        $identifierParts = explode(':', $id);
        $product = $identifierParts[0];
        $work = $identifierParts[1];

        $file = vsprintf('/pdf/%s/%s/%s.pdf', [$product, $work, $work]);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');

        if ($fileSystem->has($file)) {
            $response->setContent($fileSystem->read($file));
            return $response;
        } else {
            throw new NotFoundHttpException(sprintf('PDF file for %s not found', $id));
        }
    }
}
