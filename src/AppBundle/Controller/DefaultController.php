<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TableOfContents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/content/{action}", name="_content")
     */
    public function contentAction($action)
    {

        $file = __DIR__ . '/../../../app/Resources/content/' . $action . '.md';

        if (file_exists($file)) {
            $text = file_get_contents($file);
            $content = $this->get('markdown.parser')->transformMarkdown($text);
        } else {
            return $this->redirect($this->generateUrl('subugoe_find_homepage'));
        }

        return $this->render('partials/site/content.html.twig', ['content' => $content]);
    }

    /**
     * @Route("/{id}/mets.xml", name="_mets")
     * @param string $id
     * @return RedirectResponse
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

}
