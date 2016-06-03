<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CollectionController extends Controller
{
    /**
     * @Route("/collections", name="_collections", methods={"GET"})
     */
    public function indexAction()
    {
        $collections = $this->getParameter('collections');

        return $this->render('collections.html.twig', ['collections' => $collections]);
    }

    /**
     * @Route("/collection/{id}", name="_collection", methods={"GET"})
     *
     * @todo avoid code duplication!
     */
    public function collectionAction(Request $request, $id)
    {
        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $query = $request->get('q') ?: '';
        $facetConfiguration = $this->getParameter('facets');

        $paginator = $this->get('knp_paginator');
        $rows = (int) $this->getParameter('results_per_page');

        $filterQuery = new FilterQuery();
        $filterQuery->setKey('dc');
        $filterQuery->setQuery('dc:'.$id);

        $workQuery = new FilterQuery();
        $workQuery->setKey('work');
        $workQuery->setQuery('iswork:true');

        $select->addFilterQueries([$filterQuery, $workQuery]);

        $currentPage = (int) $request->get('page') ?: 1;
        $offset = ($currentPage - 1) * $rows;

        $facetSet = $select->getFacetSet();

        foreach ($facetConfiguration as $facet) {
            $facetSet->createFacetField($facet['title'])->setField($facet['field']);
        }

        $activeFacets = $request->get('filter');
        $facetCounter = count($activeFacets) ?: 0;

        if (count($activeFacets) > 0) {
            foreach ($activeFacets as $activeFacet) {
                $filterQuery = new FilterQuery();
                foreach ($activeFacet as $itemKey => $item) {
                    $filterQuery->setKey($itemKey.$facetCounter);
                    $filterQuery->setQuery($itemKey.':"'.$item.'"');
                }
                $select->addFilterQuery($filterQuery);
                ++$facetCounter;
            }
        }

        $results = $client->select($select);

        $pagination = $paginator->paginate(
            [
                $client,
                $select,
            ],
            $currentPage,
            $rows
        );

        return $this->render('@SubugoeFind/Default/index.html.twig', [
            'pagination' => $pagination,
            'query' => $query,
            'facets' => $results->getFacetSet()->getFacets(),
            'facetCounter' => $facetCounter,
            'queryParams' => $request->get('filter') ?: [],
            'offset' => $offset,
        ]);
    }
}
