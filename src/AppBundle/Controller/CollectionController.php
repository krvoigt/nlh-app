<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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

        $filters = array_merge($this->get('subugoe_find.query_service')->addFacets($facetSet, $activeFacets), $this->getDefaultFilters($id));

        foreach ($filters as $filter) {
            $select->addFilterQuery($filter);
        }

        $results = $client->select($select);

        if ($results->count() === 0) {
            throw new NotFoundHttpException(sprintf('Collection »%s« does not exist', $id));
        }

        $file = $this->get('kernel')->getRootDir().'/Resources/content/dc/'.$id.'.md';
        $content = '';
        if (file_exists($file)) {
            $content = file_get_contents($file);
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
            'content' => $content,
            'pagination' => $pagination,
            'query' => $query,
            'facets' => $results->getFacetSet()->getFacets(),
            'facetCounter' => $this->getFacetCounter($activeFacets),
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

    /**
     * @param $activeFacets
     *
     * @return int
     */
    protected function getFacetCounter($activeFacets)
    {
        $facetCounter = count($activeFacets) ?: 0;

        return $facetCounter;
    }
}
