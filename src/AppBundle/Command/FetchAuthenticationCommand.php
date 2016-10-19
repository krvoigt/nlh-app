<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Cookie\CookieJar;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class FetchAuthenticationCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:fetch:authentication')
            ->setDescription('Get Authentication information');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
        $this->getAuthenticationFiles();
        $output->writeln('Downloaded authentication files');
        $this->getIps();
    }

    /**
     * Downloads and stores files with authenticatable ip addresses
     */
    protected function getAuthenticationFiles()
    {
        $client = $this->getContainer()->get('guzzle.client.auth');
        $jar = new CookieJar();
        $client->post('login_form', [
            'cookies' => $jar,
            'form_params' => [
                'form.submitted' => 1,
                '__ac_name' => $this->getContainer()->getParameter('auth_data')['username'],
                '__ac_password' => $this->getContainer()->getParameter('auth_data')['password'],
            ],
        ]);

        $keys = $this->getContainer()->getParameter('auth_keys');

        foreach ($keys as $key) {
            $csv = $client->get(
            '/nllicencemodel_export?lmuid='.$key['key'].'&wfstate=authorized&mtype=text/csv',
            [
                'cookies' => $jar,
            ]
        )->getBody()->__toString();

            $fs = $this->getContainer()->get('filesystem');
            $target = $this->getContainer()->getParameter('kernel.root_dir').'/../var/auth/'.$key['title'].'.csv';

            $fs->dumpFile($target, $csv);
        }
    }

    protected function getIps()
    {
        $dir = $this->getContainer()->getParameter('kernel.root_dir').'/../var/auth';

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

        $product = 'prod';
        $institution = 'inst';

        $ipThirdPartArr = [];
        $ipThirdPartElement = '';
        foreach ($ipv4Ips as $k => $ipv4Ip) {
            if (strchr($ipv4Ip, '*')) {
                $ipv4Ip = str_replace('*', '0-255', $ipv4Ip);
            }

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
                                $this->addUser($ipPart.'.'.$rangeIp, $institution, $product);
                            }
                            $this->entityManager->flush();
                        } else {
                            $this->addUser($ipThirdPartElement.'.'.$rangeIp.'.'.$ipFourthPart, $institution, $product);
                            $this->entityManager->flush();
                        }
                    }
                } else {
                    if (isset($ipThirdPartArr) && $ipThirdPartArr !== []) {
                        foreach ($ipThirdPartArr as $ipPart) {
                            $this->addUser($ipPart.'.'.$ipFourthPart, $institution, $product);
                        }
                        $this->entityManager->flush();
                    } else {
                        $this->addUser($ipThirdPartElement.'.'.$ipFourthPart, $institution, $product);
                        $this->entityManager->flush();
                    }
                }

                $ipThirdPartArr = [];
            } else {
                $this->addUser($ipv4Ip, $institution, $product);
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * @param string $ipAddress
     * @param string $institution
     * @param string $product
     */
    protected function addUser($ipAddress, $institution, $product)
    {
        $user = new User();
        $user
               ->setIpAddress($ipAddress)
               ->setInstitution($institution)
               ->setProduct($product);

        $this->entityManager->persist($user);
    }
}
