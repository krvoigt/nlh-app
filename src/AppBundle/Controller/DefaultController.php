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
        return new Response('Contact');
    }

    /**
     * @Route("/imprint", name="_imprint")
     */
    public function imprintAction()
    {
        return new Response('Imprint');
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
