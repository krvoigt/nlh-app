<?php

namespace AppBundle\Security\Authorization\Voter;

use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClientIpVoter implements VoterInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        // you won't check against a user attribute, so return true
        return true;
    }

    public function supportsClass($class)
    {
        // your voter supports all type of token classes, so return true
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $clientIp = $this->container->get('request_stack')->getMasterRequest()->getClientIp();
        //$clientIp = '143.93.144.1';

        $repository = $this->container->get('doctrine')->getRepository('AppBundle:User');

        $user = $repository->compareIp(ip2long($clientIp));

        if (count($user) >= 1) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
