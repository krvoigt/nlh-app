<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TableOfContents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * @Route("/id/{id}/toc/", name="_toc")
     */
    public function tocAction($id)
    {
        $client = $this->get('guzzle.client.mets');
        $file = $client
            ->get($id.'.xml')
            ->getBody()->__toString();

        $crawler = new Crawler();
        $crawler->addContent($file);

        $storage = [];

        $storage[] = $crawler
            ->filterXPath('//mets:mets/mets:structMap/mets:div')
            ->children()
            ->each(function (Crawler $node, $i) {

                $toc = $this->getTocElement($node);

                if ($node->children()->count() > 0) {
                    $children = new \SplObjectStorage();

                    $node->children()
                        ->each(function (Crawler $childNode) use (&$children) {

                            $childToc = $this->getTocElement($childNode);
                            $children->attach($childToc);
                        });
                    $toc->setChildren($children);
                }

                return $toc;
            });

        return $this->render('toc.html.twig', [
            'structure' => $storage,
            'id' => $id,
        ]);
    }

    /**
     * @param Crawler $node
     *
     * @return TableOfContents
     */
    protected function getTocElement($node)
    {
        $toc = new TableOfContents();

        $toc->setId($node->attr('ID'));
        $toc->setType($node->attr('TYPE'));
        $toc->setDmdid($node->attr('DMDID'));
        $toc->setLabel($node->attr('LABEL'));

        return $toc;
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
