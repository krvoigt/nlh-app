<?php

namespace AppBundle\Service;

use AppBundle\Model\User;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for determining access control.
 */
class AuthorizationService
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var RequestStack
     */
    protected $request;

    /**
     * AuthorizationService constructor.
     *
     * @param RegistryInterface $doctrine
     * @param RequestStack      $request
     */
    public function __construct(RegistryInterface $doctrine, RequestStack $request)
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
    }

    /*
     * Returns the user name as well as his allowed products
     *
     * @Return AppBundle\Model\User a user object
     */
    public function getAllowedProducts()
    {
        $clientIp = $this->request->getMasterRequest()->getClientIp();
        //$clientIp = '143.93.144.1';
        $repository = $this->doctrine->getRepository('AppBundle:User');
        $user = new User();

        $userArr = $repository->compareIp(ip2long($clientIp));

        if (count($userArr) > 0) {
            $user->setInstitution($userArr[0]->getInstitution());
            foreach ($userArr as $userProduct) {
                $user->addProduct($userProduct->getProduct());
            }
        }

        return $user;
    }
}
