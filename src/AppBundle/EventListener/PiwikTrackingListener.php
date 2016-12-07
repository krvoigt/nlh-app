<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use AppBundle\Service\AuthorizationService;
use AppBundle\Service\DocumentService;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class PiwikTrackingListener
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var DocumentService
     */
    private $documentService;

    const BOOK_REPORT_1 = 'bookReport1';

    const BOOK_REPORT_2 = 'bookReport2';

    const DATABASE_REPORT_1 = 'databaseReport1';

    const PLATFORM_REPORT_1 = 'platformReport1';

    /**
     * PiwikTrackingListener constructor.
     *
     * @param AuthorizationService $authorizationService
     * @param DocumentService      $documentService
     * @param Container            $container
     */
    public function __construct(AuthorizationService $authorizationService, DocumentService $documentService, Container $container)
    {
        $this->authorizationService = $authorizationService;
        $this->documentService = $documentService;
        $this->container = $container;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $queryString = urldecode($event->getRequest()->getQueryString());
        $isThisASearchResult = explode('=', explode('?', $queryString)[0]);
        $action = $event->getRequest()->get('_route');

        if (!empty($action)) {
            $client = $this->container->get('guzzle.client.piwiktracker');
            $user = $this->authorizationService->getAllowedProducts();
            $userIdentifier = $user->getIdentifier();

            if (!empty($userIdentifier)) {
                $idsite = $this->container->getParameter('piwik_idsite');
                $idsiteStr = 'idsite='.$idsite;
                $recStr = 'rec=1';

                if ($action === '_detail') {
                    $id = $event->getRequest()->get('id');
                    $page = $event->getRequest()->get('page');
                    $documentId = $id;
                    $document = $this->documentService->getDocumentById($documentId);

                    if ($document->product) {
                        $product = $document->product;
                    }

                    if (strchr($id, '|')) {
                        $idArr = explode('|', $id);
                        $documentId = $idArr[0];
                        if (isset($idArr[1]) && !empty($idArr[1])) {
                            $activeChapterId = $idArr[1];
                        }
                    }

                    // Stores Result Clicks and Record Views  (Book Report 1)
                    if ((isset($documentId) && !empty($documentId)) &&
                            (isset($userIdentifier) && !empty($userIdentifier)) &&
                            (isset($product) && !empty($product))
                    ) {
                        if ($page === null) {
                            $searchFlag = false;

                            if (!empty($isThisASearchResult[1]) && str_replace('/', '', $isThisASearchResult[1]) === 'search') {
                                $searchFlag = str_replace('/', '', $isThisASearchResult[1]);
                            }

                            $bookReport1TrackingIdentifier = $userIdentifier.':'.$documentId.':'.$product;

                            if ($searchFlag) {
                                $bookReport1TrackingIdentifier .= ':'.$searchFlag;
                            }

                            if (!empty($bookReport1TrackingIdentifier)) {
                                $cvar = 'cvar={"1":["'.self::BOOK_REPORT_1.'","'.$bookReport1TrackingIdentifier.'"]}';
                                $trackingRequest = '?'.$cvar.'&'.$idsiteStr.'&'.$recStr;
                                $client->post($trackingRequest);
                            }
                        }

                        // Stores Book Chapter Clicks (Book Report 2)
                        if (isset($activeChapterId) && !empty($activeChapterId)) {
                            $bookReport2TrackingIdentifier = $documentId.':'.$activeChapterId.':'.$userIdentifier.':'.$product;

                            if (!empty($bookReport2TrackingIdentifier)) {
                                $cvar = 'cvar={"2":["'.self::BOOK_REPORT_2.'","'.$bookReport2TrackingIdentifier.'"]}';
                                $trackingRequest = '?'.$cvar.'&'.$idsiteStr.'&'.$recStr;
                                $client->post($trackingRequest);
                            }
                        }
                    }
                } elseif ($action === '_search' || $action === '_search_advanced') {
                    switch ($action) {
                        case '_search':
                            $collection = $event->getRequest()->get('collection');
                        break;
                        case '_search_advanced':
                            $collection = $event->getRequest()->get('advanced_search')['product'];
                        break;
                    }

                    // Stores Regular Searches in products (Database Report 1)
                    if (!empty($collection) && $collection !== 'all') {
                        $searchTrackingIdentifier = $userIdentifier.':'.$collection;
                        $cvar = 'cvar={"3":["'.self::DATABASE_REPORT_1.'","'.$searchTrackingIdentifier.'"]}';
                    // Stores global Searches (Platform Report 1)
                    } else {
                        $searchTrackingIdentifier = $userIdentifier;
                        $cvar = 'cvar={"4":["'.self::PLATFORM_REPORT_1.'","'.$searchTrackingIdentifier.'"]}';
                    }

                    $trackingRequest = '?'.$cvar.'&'.$idsiteStr.'&'.$recStr;

                    $client->post($trackingRequest);
                }
            }
        }
    }
}
