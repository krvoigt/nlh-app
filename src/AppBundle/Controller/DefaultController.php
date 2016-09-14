<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Subugoe\FindBundle\Controller\DefaultController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Solarium\QueryType\Select\Query\FilterQuery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Entity\Pagination;

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
     * @Route("/id/{id}", name="_detail")
     *
     * @param string $id The document id
     *
     * @return Response
     */
    public function detailAction($id)
    {
        $pagination = new Pagination();

        $request = $this->get('request_stack')->getCurrentRequest();

        if ($request->get('page')) {
            $page = $request->get('page');
        } else {
            $page = 1;
        }

        $documentId = $id;

        if (strchr($id, '|')) {
            $documentId = explode('|', $id)[0];
        }

        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $select->setQuery('id:'.$documentId);
        $document = $client->select($select);
        $document = $document->getDocuments();
        if (count($document) === 0) {
            throw new NotFoundHttpException(sprintf('Document %s not found', $documentId));
        }

        if (isset($document[0]->presentation_url[0])) {
            $identifier = explode('/', explode('.', $document[0]->presentation_url[0])[3])[3];
            $identifier = $documentId.':'.str_pad($page, strlen($identifier), 0, STR_PAD_LEFT);
        }

        $metsService = $this->get('mets_service');
        $pageMappings = $metsService->getScannedPagesMapping($documentId);
        $pageCount = count($pageMappings);
        $structure = $metsService->getTableOfContents($documentId);
        $tableOfContents = count($structure[0]) > 0 ? true : false;

        if (count($structure[0]) > 0) {
            $chapterArr = $this->flattenStructure(array_filter($structure[0]));
            $firstChapter = $chapterArr[0]['chapterId'];
            $lastChapter = $chapterArr[count($chapterArr) - 1]['chapterId'];
        }

        if ($pageMappings !== []) {
            $documentFirstPage = array_keys($pageMappings)[0];
            $documentLastPage = array_keys($pageMappings)[count($pageMappings) - 1];
        }

        $pagination->setPage($page);
        $pagination->setPageCount($pageCount);
        $pagination->setTableOfContents($tableOfContents);
        $pagination->setIdentifier(isset($identifier) ? $identifier : null);
        $pagination->setFirstChapter(isset($firstChapter) ? $firstChapter : null);
        $pagination->setLastChapter(isset($lastChapter) ? $lastChapter : null);
        $pagination->setDocumentFirstPage(isset($documentFirstPage) ? $documentFirstPage : null);
        $pagination->setDocumentLastPage(isset($documentLastPage) ? $documentLastPage : null);

        return $this->render('SubugoeFindBundle:Default:detail.html.twig', [
                        'document' => $document[0]->getFields(),
                        'pageMappings' => $pageMappings,
                        'pagination' => $pagination,
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

    /*
     * This flattens the document structure for navigation
     *
     * @param array $structure The document structure
     *
     * @return array $chapterArr The flattend chapter structure
     */
    protected function flattenStructure($structure)
    {
        $chapterArr = [];
        foreach ($structure as $chapter) {
            if (count($chapter->getChildren()) > 0) {
                $chapterArr = array_merge($chapterArr, $this->flattenStructure($chapter->getChildren()));
            } else {
                $chapterArr[] = ['chapterId' => $chapter->getId(),
                        'chapterFirstPage' => $chapter->getPhysicalPages()[0],
                        'chapterLastPage' => $chapter->getPhysicalPages()[count($chapter->getPhysicalPages()) - 1],
                ];
            }
        }

        return $chapterArr;
    }
}
