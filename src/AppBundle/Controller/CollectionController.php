<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\Component\FacetSet;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CollectionController extends Controller
{
    /**
     * @Route("/collections", name="_collections", methods={"GET"})
     */
    public function indexAction(): Response
    {
        $collections = $this->getParameter('collections');

        return $this->render('collections.html.twig', ['collections' => $collections]);
    }

    /**
     * @Route("/collection/{id}", name="_collection", methods={"GET"})
     */
    public function collectionAction(Request $request, $id)
    {
        $client = $this->get('solarium.client');
        $paginator = $this->get('knp_paginator');
        $activeFacets = $request->get('filter');

        $query = $request->get('q') ?: '';
        $currentPage = (int) $request->get('page') ?: 1;

        $rows = (int) $this->getParameter('results_per_page');

        $offset = ($currentPage - 1) * $rows;

        $select = $client->createSelect();

        $facetSet = $select->getFacetSet();

        $filters = array_merge($this->addFacets($facetSet, $activeFacets), $this->getDefaultFilters($id));

        foreach ($filters as $filter) {
            $select->addFilterQuery($filter);
        }

        $results = $client->select($select);

        if ($results->count() === 0) {
            throw new NotFoundHttpException(sprintf('Collection »%s« does not exist', $id));
        }

        $pagination = $paginator->paginate(
            [
                $client,
                $select,
            ],
            $currentPage,
            $rows
        );

        return $this->render('@SubugoeFind/Default/collections.html.twig', [
            'pagination' => $pagination,
            'query' => $query,
            'facets' => $results->getFacetSet()->getFacets(),
            'facetCounter' => $this->getFacetCounter($request),
            'queryParams' => $request->get('filter') ?: [],
            'offset' => $offset,
        ]);
    }

    /**
     * @param string $id
     *
     * @return array
     */
    protected function getDefaultFilters($id)
    {
        $filterQueries = [];

        $dcFilter = new FilterQuery();
        $dcFilter->setKey('dc')->setQuery('dc:'.$id);

        $workQuery = new FilterQuery();
        $workQuery->setKey('work')->setQuery('iswork:true');

        $filterQueries[] = $dcFilter;
        $filterQueries[] = $workQuery;

        return $filterQueries;
    }

    protected function getFacetCounter($activeFacets)
    {
        $facetCounter = count($activeFacets) ?: 0;

        return $facetCounter;
    }

    /**
     * @param FacetSet $facetSet
     * @param array    $activeFacets
     *
     * @return array
     */
    protected function addFacets(FacetSet $facetSet, $activeFacets)
    {
        $facetConfiguration = $this->getParameter('facets');

        $filterQueries = [];
        $facetCounter = $this->getFacetCounter($activeFacets);
        foreach ($facetConfiguration as $facet) {
            $facetSet->createFacetField($facet['title'])->setField($facet['field']);
        }

        if (count($activeFacets) > 0) {
            foreach ($activeFacets as $activeFacet) {
                $filterQuery = new FilterQuery();
                foreach ($activeFacet as $itemKey => $item) {
                    $filterQuery->setKey($itemKey.$this->getFacetCounter($activeFacets));
                    $filterQuery->setQuery(vsprintf('%s:"%s"', [$itemKey, $item]));
                }
                $filterQueries[] = $filterQuery;
                ++$facetCounter;
            }
        }

        return $filterQueries;
    }
}
