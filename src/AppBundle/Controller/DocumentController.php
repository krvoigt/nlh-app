<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * @Route("/id/{id}/toc/", name="_toc")
     */
    public function tocAction($id)
    {
        $client = $this->get('guzzle.client.mets');
        $metsService = $this->get('mets_service');

        $file = $client
            ->get($id.'.xml')
            ->getBody()->__toString();

        $structure = $metsService->getTableOfContents($file);

        return $this->render('toc.html.twig', [
            'structure' => $structure,
            'id' => $id,
        ]);
    }

    /**
     * @param string $url
     *
     * @return Response
     */
    public function fullTextAction($url)
    {
        $client = $this->get('guzzle.client.fulltext');
        $teiProcessor = $this->get('tei_processor');

        $file = $client
            ->get($url)
            ->getBody();

        $text = $teiProcessor->process($file);

        return new Response($text);
    }

    /**
     * @Route("/{id}/mets.xml", name="_mets")
     *
     * @param string $id
     *
     * @return Response
     */
    public function metsAction($id)
    {
        $client = $this->get('guzzle.client.mets');
        $file = $client
            ->get($id.'.xml')
            ->getBody();

        $response = new Response(
            $file,
            Response::HTTP_OK,
            [
                'content-type' => 'application/mets+xml',
            ]
        );

        return $response;
    }

    /**
     * @Route("/just-scanned/", name="just-scanned")
     */
    public function justScannedAction()
    {
        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $select->setRows(1);
        $select->addSort('dateindexed', 'desc');

        return $this->render('partials/app/just-scanned.html.twig', [
            'document' => $client->select($select)->getDocuments()[0],
        ]);
    }
}
