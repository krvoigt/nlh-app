<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Result\DocumentInterface;
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

        $selectParentDocument = $client->createSelect()
                ->setQuery(sprintf('id:%s', $id));
        $parentDocument = $client
                ->select($selectParentDocument)
                ->getDocuments()[0]
                ->getFields();

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
     * @param DocumentInterface $document
     *
     * @return bool
     */
    protected function onlyOneParent($document)
    {
        if (count($document->getFields()['idparentdoc']) === 1) {
            return true;
        } else {
            return false;
        }
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
     * @Route("/{id}/tei.xml", name="_tei", methods={"GET"})
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
     * @Route("/{id}/mets.xml", name="_mets", methods={"GET"})
     *
     * @param string $id
     *
     * @return Response
     */
    public function metsAction($id)
    {
        $client = $this->get('solarium.client');
        $select = $client->createSelect()->setQuery(sprintf('id:%s', $id));
        $document = $client->select($select)->getDocuments()[0];

        $identifier = $document->presentation_url[0];
        $identifier = str_replace('https://nl.sub.uni-goettingen.de/image/', '', $identifier);
        $identifier = str_replace('/full/full/0/default.jpg', '', $identifier);
        $identifierParts = explode(':', $identifier);

        $file = $this->get('oneup_flysystem.nlh_filesystem')->read(vsprintf('/mets/%s/%s.mets.xml', [$identifierParts[0], $identifierParts[1]]));

        $response = new Response(
            $file,
            Response::HTTP_OK,
            [
                'content-type' => 'application/xml',
            ]
        );

        return $response;
    }

    /**
     * @Route("/just-scanned/", name="just-scanned", methods={"GET"})
     */
    public function justScannedAction()
    {
        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $select->setRows(1);
        $select->addSort('date_indexed', 'desc');

        return $this->render('partials/app/just-scanned.html.twig', [
            'document' => $client->select($select)->getDocuments()[0],
        ]);
    }
}
