<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolverController extends Controller
{
    /**
     * @Route("/resolve", name="_resolve", methods={"GET","POST"})
     */
    public function indexAction(Request $request)
    {
        $pid = $request->get('PID');

        if (!$request->get('PID') || !$this->isValidId($pid)) {
            throw new NotFoundHttpException('Page not found');
        }

        $router = $this->get('router');
        $response = new Response();

        $route = $router->generate(
            '_detail', [
                'id' => $pid,
            ],
            $router::ABSOLUTE_URL
        );
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');

        return $this->render(
            'resolve/resolve.xml.twig', [
                'route' => $route,
            ],
            $response
        );
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    private function isValidId($id)
    {
        $client = $this->get('solarium.client');
        $select = $client
            ->createSelect()
            ->setQuery(sprintf('id:%s', $id));
        $document = $client
                      ->select($select);

        if ($document->count() === 1) {
            return true;
        }

        return false;
    }
}
