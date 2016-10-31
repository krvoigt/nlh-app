<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Subugoe\FindBundle\Entity\Search;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CollectionController extends Controller
{
    /**
     * @Route("/", name="_homepage", methods={"GET"})
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
        $user = $this->get('authorization_service')->getAllowedProducts();

        $client = $this->get('solarium.client');
        $paginator = $this->get('knp_paginator');
        $activeFacets = $request->get('filter');

        $search = new Search();
        $search
            ->setQuery($request->get('q') ?: '')
            ->setRows((int) $this->getParameter('results_per_page'))
            ->setCurrentPage((int) $request->get('page') ?: 1);

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

        $pagination = $paginator->paginate(
            [
                $client,
                $select,
            ],
            $search->getCurrentPage(),
            $search->getRows()
        );

        return $this->render('@SubugoeFind/Default/collections.html.twig', [
            'content' => $this->getCollectionContent($id),
            'pagination' => $pagination,
            'search' => $search,
            'facets' => $results->getFacetSet()->getFacets(),
            'facetCounter' => $this->get('subugoe_find.query_service')->getFacetCounter($activeFacets),
            'queryParams' => $request->get('filter') ?: [],
            'activeCollection' => $id,
            'user' => $user,
        ]);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getCollectionContent($id)
    {
        $file = $this->get('kernel')->getRootDir().'/Resources/content/dc/'.$id.'.md';
        $content = '';
        if (file_exists($file)) {
            $content = file_get_contents($file);
        }

        return $content;
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
        $dcFilter->setKey('facet_product')->setQuery('facet_product:'.$id);

        $filterQueries[] = $dcFilter;

        return $filterQueries;
    }
}
