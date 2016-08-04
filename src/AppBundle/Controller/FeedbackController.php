<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\FeedbackType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FeedbackController extends Controller
{
    /**
     * @Route("/feedback/", name="_feedback", methods={"GET","POST"})
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(FeedbackType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipient = $this->getParameter('feedback_form_recipient');
            $sender = $this->getParameter('feedback_form_sender');
            $referer = $this->getRefererParams($request);

            $message = \Swift_Message::newInstance()
                        ->setSubject('GDZ Feedback zu '.$referer)
                        ->setFrom($sender)
                        ->setTo($recipient)
                        ->setBody(
                            $this->renderView(
                                'email/feedback.html.twig',
                                [
                                    'form' => $form->getData(),
                                    'referer' => $referer,
                                ]
                            ),
                            'text/html'
                        );
            $this->get('mailer')->send($message);
            $this->addFlash('notice', 'Your feedback has been sent.');

            $response = $this->redirectToRoute('_homepage');

            return $response;
        }

        return $this->render('feedback.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getRefererParams(Request $request)
    {
        $referer = $request->headers->get('referer');

        return $referer;
    }
}
