<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/{action}", name="_content")
     */
    public function contentAction($action)
    {

        $file = __DIR__ . '/../../../app/Resources/content/' . $action . '.md';

        if (file_exists($file)) {
            $text = file_get_contents($file);
            $content = $this->container->get('markdown.parser')->transformMarkdown($text);
        } else {
            return $this->redirect($this->generateUrl('subugoe_find_homepage'));
        }

        return $this->render('partials/site/content.html.twig', ['content' => $content]);
    }

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
