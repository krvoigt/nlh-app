<?php

namespace AppBundle\Security\Authorization\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Finder\Finder;
use League\Csv\Reader;

class ClientIpVoter implements VoterInterface
{
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
        $allowedIpList = $this->getIps();
        $clientIp = $this->container->get('request_stack')->getMasterRequest()->getClientIp();
        // Test-ip
        //$clientIp = '141.15.29.242';
        if (in_array($clientIp, $allowedIpList)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    protected function getIps() {
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $dir = $rootDir.'/../var/auth';

        $finder = new Finder();
        $files = $finder->in($dir);

        $ipv4Ips = [];
        foreach ($files as $file) {
            $reader = Reader::createFromPath($file);
            $reader->setDelimiter(';');
            $keys = ['user_name', 'status', 'title', 'street', 'zip', 'city', 'county', 'country', 'telephone', 'fax', 'email', 'url', 'contactperson', 'sigel', 'ezb_id', 'subscriper_group', 'ipv4_allow', 'ipv4_deny', 'zuid', 'mtime', 'mtime_license', 'status_license', 'mtime_status'];

            $results = $reader->fetchAssoc($keys);

            foreach ($results as $key => $row) {
                if ($key > 0) {
                    $ipv4 = explode(',', $row['ipv4_allow']);
                    $ipv4Ips = array_merge($ipv4Ips, $ipv4);
                }
            }
        }

        $allowedIpList = [];
        $ipThirdPartArr = [];
        $ipThirdPartElement = '';
        foreach ($ipv4Ips as $k => $ipv4Ip) {

            if (strchr($ipv4Ip, '*')) $ipv4Ip = str_replace('*', '0-255', $ipv4Ip);

            if (strchr($ipv4Ip, '-')) {
                $ipParts = explode('.', $ipv4Ip);
                $ipFirstPart = $ipParts[0];
                $ipSecondPart = $ipParts[1];
                $ipThirdPart = $ipParts[2];
                $ipFourthPart = $ipParts[3];

                if (strchr($ipThirdPart, '-')) {
                    $rangeNumber = explode('-', $ipThirdPart);
                    $rangeIps = range($rangeNumber[0], $rangeNumber[1]);
                    foreach ($rangeIps as $rangeIp) {
                        $ipThirdPartArr[] = $ipFirstPart.'.'.$ipSecondPart.'.'.$rangeIp;
                    }
                } else {
                    $ipThirdPartElement = $ipFirstPart.'.'.$ipSecondPart.'.'.$ipThirdPart;
                }

                if (strchr($ipFourthPart, '-')) {
                    $rangeNumber = explode('-', $ipFourthPart);
                    $rangeIps = range($rangeNumber[0], $rangeNumber[1]);
                    foreach ($rangeIps as $rangeIp) {
                        if (isset($ipThirdPartArr) && $ipThirdPartArr !== []) {
                            foreach ($ipThirdPartArr as $ipPart) {
                                $allowedIpList[] = $ipPart.'.'.$rangeIp;
                            }
                        } else {
                            $allowedIpList[] = $ipThirdPartElement.'.'.$rangeIp;
                        }
                    }
                } else {
                    if (isset($ipThirdPartArr) && $ipThirdPartArr !== []) {
                        foreach ($ipThirdPartArr as $ipPart) {
                            $allowedIpList[] = $ipPart.'.'.$ipFourthPart;
                        }
                    } else {
                        $allowedIpList[] = $ipThirdPartElement.'.'.$ipFourthPart;
                    }
                }

                $ipThirdPartArr = [];
            } else {
                $allowedIpList[] = $ipv4Ip;
            }
        }

        return $allowedIpList;
    }
}
