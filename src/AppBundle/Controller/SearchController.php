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
    public function indexAction(Request $request)
    {
        $form = $this->createForm(AdvancedSearchType::class, null, [
                  'search_fields' => $this->getParameter('advanced_search'),
                  'translator' => $this->get('translator'),
              ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $queryString = [];

            foreach ($form->getData() as $field => $datum) {
                if ($datum !== null) {
                    $queryString[] = $field .':'.$datum;
                }
            }
          return new Response(implode(' ', $queryString));
        }

        return $this->render('search/advanced.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
