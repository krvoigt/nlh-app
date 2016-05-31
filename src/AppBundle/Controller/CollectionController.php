<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CollectionController extends Controller
{
    /**
     * @Route("/", name="_homepage")
     */
    public function indexAction()
    {
        $collections = $this->getParameter('collections');

        return $this->render('collections.html.twig', ['collections' => $collections]);
    }

    /**
     * @Route("/collection/{id}", name="_collection")
     */
    public function collectionAction(Request $request, $id)
    {
        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $query = $request->get('q') ?: '*:*';

        $filterQuery = new FilterQuery();
        $filterQuery->setKey('dc');
        $filterQuery->setQuery('dc:'.$id);

        $workQuery = new FilterQuery();
        $workQuery->setKey('work');
        $workQuery->setQuery('iswork:true');

        $select->addFilterQueries([$filterQuery, $workQuery]);

        $paginator = $this->get('knp_paginator');
        $rows = (int) $this->getParameter('results_per_page');
        $currentPage = (int) $request->get('page') ?: 1;

        $offset = ($currentPage - 1) * $rows;

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
            'facets' => [],
            'offset' => $offset,
        ]);
    }
}
