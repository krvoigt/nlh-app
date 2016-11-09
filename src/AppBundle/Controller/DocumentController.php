<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * @Route("/anchor/{id}", name="_anchor", methods={"GET"})
     */
    public function anchorDocumentAction($id)
    {
        $client = $this->get('solarium.client');

        $parentDocument = $this->get('document_service')->getDocumentById($id);

        $selectChildrenDocuments = $client->createSelect()->setRows(500)
                ->addSort('currentno', 'ASC')
                ->setQuery(sprintf('idparentdoc:%s AND docstrct:volume', $id));
        $childrenDocuments = $client->select($selectChildrenDocuments)->getDocuments();

        return $this->render('partials/app/anchor.html.twig', [
            'parentDocument' => $parentDocument,
            'childrenDocuments' => $childrenDocuments,
        ]);
    }

    /**
     * @Route("/id/{id}/toc/", name="_toc", methods={"GET"})
     */
    public function tocAction($id)
    {
        if (strchr($id, '|')) {
            $id = explode('|', $id)[0];
        }

        $metsService = $this->get('mets_service');

        $structure = array_filter($metsService->getTableOfContents($id)[0]);

        return $this->render('toc.html.twig', [
            'structure' => $structure,
            'pageMappings' => $metsService->getScannedPagesMapping($id),
            'id' => $id,
        ]);
    }

    /**
     * @Route("/tei/{id}.tei.xml", name="_tei", methods={"GET"})
     *
     * @param string $id
     *
     * @return Response
     */
    public function fullTextAction($id)
    {
        $teiProcessor = $this->get('tei_processor');
        $identifier = str_replace('https://nl.sub.uni-goettingen.de/image/', '', $id);
        $identifier = str_replace('/full/full/0/default.jpg', '', $identifier);
        $identifierParts = explode(':', $identifier);

        $file = $this->get('oneup_flysystem.nlh_filesystem')->read(vsprintf('/tei/%s/%s/%s.tei.xml', [$identifierParts[0], $identifierParts[1], $identifierParts[2]]));

        $text = $teiProcessor->process($file);

        return new Response($text);
    }

    /**
     * @Route("/mets/{id}.mets.xml", name="_mets", methods={"GET"})
     *
     * @param string $id
     *
     * @return Response
     */
    public function metsAction($id)
    {
        $document = $this->get('document_service')->getDocumentById($id);
        $file = $this->get('oneup_flysystem.nlh_filesystem')->read(vsprintf('/mets/%s/%s.mets.xml', [$document->product, $document->work]));

        $response = new Response(
            $file,
            Response::HTTP_OK,
            [
                'content-type' => 'application/xml',
            ]
        );

        return $response;
    }
}
