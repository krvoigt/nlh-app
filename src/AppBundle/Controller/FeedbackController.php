<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController extends Controller
{
    /**
     * @Route("/feedback/", name="_feedback", methods={"GET"})
     */
    public function indexAction()
    {
        return new Response('Feedback');
    }
}
