<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
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
        $file = $this->get('kernel')->getRootDir().'/Resources/content/'.$action.'.md';

        if (file_exists($file)) {
            $text = file_get_contents($file);
            $content = $this->get('markdown.parser')->transformMarkdown($text);
        } else {
            return $this->redirect($this->generateUrl('_homepage'));
        }

        return $this->render('partials/site/content.html.twig', ['content' => $content]);
    }
}
