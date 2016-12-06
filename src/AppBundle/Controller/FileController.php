<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller implements IpAuthenticatedController
{
    /**
     * @Route("/download/pdf/{id}.pdf", name="_download_pdf")
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

    /**
     * @Route("/download/{id}.{_format}",
     *      name="_download_export",
     *      requirements={
     *          "_format": "ris|bib|enl",
     *     }
     * )
     */
    public function bibliographicalExportAction($id, Request $request)
    {
        $document = $this->get('document_service')->getDocumentById($id);
        $format = $request->getRequestFormat();

        $response = new Response();

        $formats = [
            'bib' => 'application/x-bibtex',
            'ris' => 'application/x-research-info-systems',
            'enl' => 'application/x-endnote-library	',
        ];

        $response->headers->set('Content-Type', $formats[$format]);

        return $this->render(
            ':export:bibliographic.'.$format . '.twig',
            [
                'document' => $document,
            ],
            $response
        );
    }

}
