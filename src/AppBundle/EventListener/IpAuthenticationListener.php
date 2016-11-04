<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\IIIFController;
use AppBundle\Controller\IpAuthenticatedController;
use AppBundle\Service\AuthorizationService;
use AppBundle\Service\DocumentService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class IpAuthenticationListener
{
    /**
     * @var AuthorizationService
     */
    protected $authorizationService;

    /**
     * @var string
     */
    protected $registrationLink;

    /**
     * @var DocumentService
     */
    protected $documentService;

    /**
     * @var string
     */
    protected $lockImage;

    public function __construct(
        AuthorizationService $authorizationService,
        DocumentService $documentService,
        $registrationLink,
        $lockImage
    ) {
        $this->authorizationService = $authorizationService;
        $this->registrationLink = $registrationLink;
        $this->documentService = $documentService;
        $this->lockImage = $lockImage;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        // we shall run on cli and only on tagged controllers
        if (php_sapi_name() === 'cli' || !($controller[0] instanceof IpAuthenticatedController)) {
            return;
        }

        $user = $this->authorizationService->getAllowedProducts();
        $products = $user->getProducts();

        if ($controller[1] === 'detailAction') {
            $id = $event->getRequest()->get('id');

            $product = $this->documentService->getDocumentById($id)->product;

            $registrationLink = $this->registrationLink;

            if (!in_array($product, $products)) {
                $event->setController(function () use ($registrationLink) {
                    return new RedirectResponse($registrationLink);
                });
            }
        }

        if ($controller[0] instanceof IIIFController and $controller[1] === 'indexAction') {
            $product = explode(':', $event->getRequest()->get('identifier'))[0];

            if (!in_array($product, $products)) {
                $event->setController(function () {
                    return new BinaryFileResponse($this->lockImage);
                });
            }
        }
    }
}
