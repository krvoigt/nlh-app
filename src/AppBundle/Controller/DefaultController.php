<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/contact", name="_contact")
     */
    public function contactAction()
    {

        $file = __DIR__ . '/../../../app/Resources/content/contact.md';
        $text = file_get_contents($file);

        $content = $this->container->get('markdown.parser')->transformMarkdown($text);

        return $this->render('partials/site/content.html.twig', ['content' => $content]);
    }

    /**
     * @Route("/imprint", name="_imprint")
     */
    public function imprintAction()
    {
        $file = __DIR__ . '/../../../app/Resources/content/imprint.md';
        $text = file_get_contents($file);

        $content = $this->container->get('markdown.parser')->transformMarkdown($text);

        return $this->render('partials/site/content.html.twig', ['content' => $content]);    }

    /**
     * @Route("/mets/{id}.xml", name="_mets")
     * @param string $id
     * @return RedirectResponse
     */
    public function metsAction($id)
    {
        $file = 'http://gdz.sub.uni-goettingen.de/mets/' . $id . '.xml';
        $response = new RedirectResponse($file);
        return $response;
    }

}
