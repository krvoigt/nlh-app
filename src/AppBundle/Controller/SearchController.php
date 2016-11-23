<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\AdvancedSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends Controller
{
    /**
     * @Route("/search/advanced/", name="_search_advanced", methods={"GET"})
     */
    public function indexAction()
    {
        $form = $this->getForm();

        return $this->render('search/advanced.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/search/advanced/", name="_search_advanced_post", methods={"POST"})
     */
    public function newAction(Request $request)
    {
        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return new Response('submitted');
        }

        return $this->render('search/advanced.html.twig', ['form' => $form->createView()]);
    }

    private function getForm()
    {
        return $this->createForm(AdvancedSearchType::class, null, [
                  'search_fields' => $this->getParameter('advanced_search'),
                  'translator' => $this->get('translator'),
              ]);
    }
}
