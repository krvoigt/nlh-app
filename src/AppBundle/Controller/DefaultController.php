<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Subugoe\FindBundle\Controller\DefaultController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Solarium\QueryType\Select\Query\FilterQuery;
use AppBundle\Model\DocumentStructure;
use Subugoe\FindBundle\Entity\Search;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Solarium\QueryType\Select\Query\Query;

class DefaultController extends BaseController implements IpAuthenticatedController
{
    /**
     * @Route("/search", name="_search")
     *
     * @param Request $request A request instance
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $access = $request->get('access');
        $sort = $request->get('sort');
        if (isset($sort) && empty($sort)) {
            throw new InvalidParameterException(sprintf('Sort argument must be provided.'));
        }
        $order = $request->get('direction');
        $searchService = $this->get('subugoe_find.search_service');
        $queryService = $this->get('subugoe_find.query_service');

        $search = $searchService->getSearchEntity();
        $select = $searchService->getQuerySelect();
        $queryService->addQuerySort($select, $sort, $order);
        $activeFacets = $request->get('filter');
        $queryService->addQueryFilters($select, $activeFacets);
        $collection = $request->get('collection');
        if (!empty($collection) && $collection !== 'all') {
            $dcFilter = new FilterQuery();
            $collectionFilter = $dcFilter->setKey('dc')->setQuery('facet_product:'.$collection);
            $select->addFilterQuery($collectionFilter);
        }

        $user = $this->get('authorization_service')->getAllowedProducts();
        $products = $user->getProducts();
        sort($products);

        if ($access !== null) {
            $select = $this->addFilterForAllowedProducts($products, $select);
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

    /**
     * @Route("/id/{id}", name="_detail")
     *
     * @param string  $id      The document id
     * @param Request $request The request
     *
     * @return Response
     */
    public function detailAction($id, Request $request)
    {
        $documentStructure = new DocumentStructure();

        if ($request->get('page')) {
            $page = $request->get('page');
        } else {
            $page = 1;
        }

        $documentId = $id;

        if (strchr($id, '|')) {
            $idArr = explode('|', $id);
            $documentId = $idArr[0];
            if (isset($idArr[1]) && !empty($idArr[1])) {
                $activeChapterId = $idArr[1];
                $previousPageChapterId = $activeChapterId;
                $nextPageChapterId = $activeChapterId;
            }
        }

        $document = $this->get('document_service')->getDocumentById($documentId);

        if ($document->idparentdoc[0]) {
            $parentDocumentTitle = $this->get('document_service')->getDocumentById($document->idparentdoc[0])['title'][0];
        }

        if (isset($document->nlh_id[0])) {
            $identifier = $document->nlh_id[$page - 1];
        }

        if (!$document->isanchor) {
            $metsService = $this->get('mets_service');
            $pageMappings = $metsService->getScannedPagesMapping($documentId);
            $pageCount = count($pageMappings);
            $structure = $metsService->getTableOfContents($documentId);
            $tableOfContents = count($structure[0]) > 0 ? true : false;
        }

        if (isset($pageMappings) && $pageMappings !== []) {
            $documentFirstPage = array_keys($pageMappings)[0];
            $documentLastPage = array_keys($pageMappings)[count($pageMappings) - 1];
        }

        if (isset($structure[0]) && count($structure[0]) > 0) {
            $chapterArr = $this->flattenStructure(array_filter($structure[0]));
            $firstChapter = $chapterArr[0]['chapterId'];
            $lastChapter = $chapterArr[count($chapterArr) - 1]['chapterId'];

            if (!isset($activeChapterId)) {
                if ($page === 1) {
                    $activeChapterId = $firstChapter;
                } else {
                    $activeChapterId = $this->getChapterId($chapterArr, $page);
                }
            }

            $activeChapterkey = array_search($activeChapterId, array_column($chapterArr, 'chapterId'));
            $chapterArrLastKey = array_keys($chapterArr);
            $chapterArrLastKey = end($chapterArrLastKey);

            $activeChapterFirstPage = abs(explode('_', $chapterArr[$activeChapterkey]['chapterFirstPage'])[1]);
            $activeChapterLastPage = abs(explode('_', $chapterArr[$activeChapterkey]['chapterLastPage'])[1]);

            if ($activeChapterkey > 0) {
                $previousChapterId = $chapterArr[$activeChapterkey - 1]['chapterId'];
                $previousChapterFirstPage = abs(explode('_', $chapterArr[$activeChapterkey - 1]['chapterFirstPage'])[1]);
                $isThereAPreviousChapter = true;
            }

            if ($activeChapterkey < $chapterArrLastKey) {
                $nextChapterId = $chapterArr[$activeChapterkey + 1]['chapterId'];
                $nextChapterFirstPage = abs(explode('_', $chapterArr[$activeChapterkey + 1]['chapterFirstPage'])[1]);
                $isThereANextChapter = true;
            }

            if ($page !== 1 && !empty($activeChapterFirstPage) && $page <= $activeChapterFirstPage && isset($previousChapterId)) {
                $previousPageChapterId = $previousChapterId;
            } else {
                $previouspage = intval($page - 1);
                $previousPageChapterId = $this->getChapterId($chapterArr, $previouspage);
            }

            if (isset($activeChapterLastPage) && $page >= $activeChapterLastPage && isset($nextChapterId)) {
                $nextPageChapterId = $nextChapterId;
            } else {
                $nextPage = intval($page + 1);
                if (isset($documentLastPage) && !empty($documentLastPage) && $nextPage === $documentLastPage) {
                    $nextPage = $page;
                }
                $nextPageChapterId = $this->getChapterId($chapterArr, $nextPage);
            }
        }

        $isValidPage = true;

        $documentStructure->setPage($page);
        $documentStructure->setPageCount(isset($pageCount) ? $pageCount : null);
        $documentStructure->setIsValidPage($isValidPage);
        $documentStructure->setTableOfContents(isset($tableOfContents) ? $tableOfContents : null);
        $documentStructure->setIdentifier(isset($identifier) ? $identifier : null);
        $documentStructure->setFirstChapter(isset($firstChapter) ? $firstChapter : null);
        $documentStructure->setLastChapter(isset($lastChapter) ? $lastChapter : null);
        $documentStructure->setDocumentFirstPage(isset($documentFirstPage) ? $documentFirstPage : null);
        $documentStructure->setDocumentLastPage(isset($documentLastPage) ? $documentLastPage : null);
        $documentStructure->setIsThereAPreviousChapter(isset($isThereAPreviousChapter) ? $isThereAPreviousChapter : false);
        $documentStructure->setIsThereANextChapter(isset($isThereANextChapter) ? $isThereANextChapter : false);
        $documentStructure->setPreviousChapterId(isset($previousChapterId) ? $previousChapterId : null);
        $documentStructure->setPreviousChapterFirstPage(isset($previousChapterFirstPage) ? $previousChapterFirstPage : null);
        $documentStructure->setNextChapterId(isset($nextChapterId) ? $nextChapterId : null);
        $documentStructure->setNextChapterFirstPage(isset($nextChapterFirstPage) ? $nextChapterFirstPage : null);
        $documentStructure->setNextPageChapterId(isset($nextPageChapterId) ? $nextPageChapterId : null);
        $documentStructure->setPreviousPageChapterId(isset($previousPageChapterId) ? $previousPageChapterId : null);

        return $this->render('SubugoeFindBundle:Default:detail.html.twig', [
                        'document' => $document->getFields(),
                        'parentDocumentTitle' => isset($parentDocumentTitle) ? $parentDocumentTitle : null,
                        'pageMappings' => isset($pageMappings) ? $pageMappings : null,
                        'documentStructure' => $documentStructure,
                        'userName' => isset($userName) ? $userName : null,
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

    /**
     * @Route("/volumes/id/{id}", name="_volumes", methods={"GET"})
     *
     * @param Request $request A request instance
     * @param string  $id      The document id
     *
     * @return Response
     */
    public function volumesAction(Request $request, $id)
    {
        $sort = $request->get('sort');
        if (isset($sort) && empty($sort)) {
            throw new InvalidParameterException(sprintf('Sort argument must be provided.'));
        }

        $client = $this->get('solarium.client');
        $searchService = $this->get('subugoe_find.search_service');
        $queryService = $this->get('subugoe_find.query_service');

        $search = $searchService->getSearchEntity();
        $select = $searchService->getQuerySelect();
        $queryService->addQuerySort($select);
        $activeFacets = $request->get('filter');
        $queryService->addQueryFilters($select, $activeFacets);
        $facets = $client->select($select)->getFacetSet()->getFacets();
        $facetCounter = $this->get('subugoe_find.query_service')->getFacetCounter($activeFacets);

        $parentDocument = $this->get('document_service')->getDocumentById($id);

        $selectChildrenDocuments = $client->createSelect()->setRows((int) 500);

        if (isset($sort) && !empty($sort)) {
            $selectChildrenDocuments->addSort(trim($sort), 'asc');
        } else {
            $selectChildrenDocuments->addSort('currentnosort', 'asc');
        }

        $selectChildrenDocuments->setQuery(sprintf('idparentdoc:%s AND docstrct:volume', $id));
        $pagination = $searchService->getPagination($selectChildrenDocuments);

        return $this->render('SubugoeFindBundle:Default:index.html.twig', [
                    'facets' => $facets,
                    'facetCounter' => $facetCounter,
                    'queryParams' => $request->get('filter') ?: [],
                    'search' => $search,
                    'pagination' => $pagination,
                    'parentDocument' => $parentDocument,
                ]);
    }

    /*
     * This flattens the document structure for navigation
     *
     * @param array $structure The document structure
     *
     * @return array $chapterArr The flattened chapter structure
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

    /*
     * This returns the chapter id for a given page
     *
     * @param array $chapterArr The chapter array
     * @param integer $page The page number
     *
     * @return string $chapterId The chapter id
     */
    protected function getChapterId($chapterArr, $page)
    {
        foreach ($chapterArr as $chapter) {
            $chapterFirstPage = ltrim(explode('_', $chapter['chapterFirstPage'])[1], 0);
            $chapterLastPage = ltrim(explode('_', $chapter['chapterLastPage'])[1], 0);

            if (in_array($page, range($chapterFirstPage, $chapterLastPage))) {
                $chapterId = $chapter['chapterId'];

                return $chapterId;
            }
        }

        return false;
    }

    /*
     * @param User  $user
     * @param Query $select
     *
     * @return Query
     */
    protected function addFilterForAllowedProducts($products, Query $select):Query
    {
        if (isset($products) && $products !== []) {
            $productQuery = [];
            foreach ($products as $key => $product) {
                $productQuery[] = sprintf('product:%s', $product);
            }
            $productQuery = implode(' OR ', $productQuery);
            $accessFilter = new FilterQuery();
            $productFilter = $accessFilter->setKey('product')->setQuery($productQuery);
            $select->addFilterQuery($productFilter);
        }

        return $select;
    }
}
