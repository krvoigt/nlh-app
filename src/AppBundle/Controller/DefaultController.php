<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Subugoe\FindBundle\Controller\DefaultController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Solarium\QueryType\Select\Query\FilterQuery;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="_homepage")
     *
     * @param Request $request A request instance
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $search = $this->getSearchEntity($request);
        $select = $this->getQuerySelect($request);
        $this->addQuerySort($select);
        $activeFacets = $request->get('filter');
        $this->addQueryFilters($select, $activeFacets);
        $collection = $request->get('collection');
        if (!empty($collection) && $collection !== 'all') {
            $dcFilter = new FilterQuery();
            $collectionFilter = $dcFilter->setKey('dc')->setQuery('dc:'.$collection);
            $select->addFilterQuery($collectionFilter);
        }
        $pagination = $this->getPagination($request, $select);
        $facets = $this->get('solarium.client')->select($select)->getFacetSet()->getFacets();
        $facetCounter = $this->get('subugoe_find.query_service')->getFacetCounter($activeFacets);

        return $this->render('SubugoeFindBundle:Default:index.html.twig', [
                'facets' => $facets,
                'facetCounter' => $facetCounter,
                'queryParams' => $request->get('filter') ?: [],
                'search' => $search,
                'pagination' => $pagination,
                'activeCollection' => $request->get('activeCollection'),
        ]);
    }

    /**
     * @Route("/content/{action}", name="_content", methods={"GET"})
     */
    public function contentAction($action)
    {
        $file = $this->get('kernel')->getRootDir().'/Resources/content/'.$action.'.md';

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            return $this->redirect($this->generateUrl('_homepage'));
        }

        return $this->render('partials/site/content.html.twig', ['content' => $content]);
    }
}
