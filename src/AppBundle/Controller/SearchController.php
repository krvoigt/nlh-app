<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\AdvancedSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends Controller implements IpAuthenticatedController
{
    /**
     * @Route("/search/advanced/", name="_search_advanced", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(AdvancedSearchType::class, null, [
            'attr' => ['class' => 'advanced-search'],
            'search_fields' => $this->getParameter('advanced_search'),
            'translator' => $this->get('translator'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $queryFragments = [];

            foreach ($form->getData() as $field => $datum) {
                if ($datum !== null) {
                    $queryFragments[] = vsprintf('%s:%s', [$field, $datum]);
                }
            }

            $query = $this->get('subugoe_find.query_service')->composeQuery(implode(' ', $queryFragments));

            $access = $request->get('access');
            $searchService = $this->get('subugoe_find.search_service');
            $queryService = $this->get('subugoe_find.query_service');
            $client = $this->get('solarium.client');

            $search = $searchService->getSearchEntity();
            $select = $client->createSelect()->setQuery($query);

            $activeFacets = $request->get('filter');
            $queryService->addQueryFilters($select, $activeFacets);

            $user = $this->get('authorization_service')->getAllowedProducts();
            $products = $user->getProducts();
            sort($products);

            if ($access !== null) {
                $select = $this->get('authorization_service')->addFilterForAllowedProducts($products, $select);
            }

            $solrProducts = $this->get('document_service')->getAvailableProducts();

            $fullAccess = ($products === $solrProducts) ? true : false;

            $pagination = $searchService->getPagination($select);
            $facets = $this->get('solarium.client')->select($select)->getFacetSet()->getFacets();
            $facetCounter = $this->get('subugoe_find.query_service')->getFacetCounter($activeFacets);

            return $this->render('SubugoeFindBundle:Default:index.html.twig', [
                'facets' => $facets,
                'facetCounter' => $facetCounter,
                'queryParams' => $request->get('filter') ?: [],
                'search' => $search,
                'pagination' => $pagination,
                'activeCollection' => $request->get('activeCollection'),
                'user' => $user,
                'access' => $access,
                'fullAccess' => $fullAccess,
            ]);
        }

        return $this->render('search/advanced.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
