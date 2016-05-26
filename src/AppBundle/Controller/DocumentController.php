<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\TableOfContents;
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
        $client   = $this->get('guzzle.client.mets');
        $file = $client
            ->get($id . '.xml')
            ->getBody()->__toString();

        $crawler = new Crawler();
        $crawler->addContent($file);
        $structure = $crawler
            ->filterXPath('//mets:mets/mets:structMap/mets:div')
            ->children()
            ->each(function (Crawler $node, $i) {
                $toc = new TableOfContents();
                $toc->setId($node->attr('ID'));
                $toc->setType($node->attr('TYPE'));
                $toc->setDmdid($node->attr('DMDID'));
                $toc->setLabel($node->attr('LABEL'));

                return $toc;
            });

        return $this->render('toc.html.twig', [
            'structure' => $structure,
            'id' => $id
        ]);
    }

    /**
     * @param string $url
     * @return Response
     */
    public function fullTextAction($url)
    {
        $client   = $this->get('guzzle.client.fulltext');
            $file = $client
                ->get($url)
                ->getBody();

        return new Response($file);
    }

    /**
     * @Route("/{id}/mets.xml", name="_mets")
     * @param string $id
     * @return Response
     */
    public function metsAction($id)
    {
        $client   = $this->get('guzzle.client.mets');
        $file = $client
            ->get($id . '.xml')
            ->getBody();

        $response = new Response(
            $file,
            Response::HTTP_OK,
            [
                'content-type' => 'application/mets+xml'
            ]
        );

        return $response;
    }
}
