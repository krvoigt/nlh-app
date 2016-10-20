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
     * @var string
     */
    protected $authenticationDirectory;

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
        $this->authenticationDirectory = $this->getContainer()->getParameter('kernel.root_dir').'/../var/auth/';

        $this->emptyAuthenticationFileDirectory();
        $output->writeln('Removed authentication files');

        $this->getAuthenticationFiles();
        $output->writeln('Downloaded authentication files');

        $importStartTime = microtime(true);
        $this->getIps();
        $importEndTime = microtime(true);
        $output->writeln('Total import completed in '.number_format($importEndTime - $importStartTime, 2).'s', true);
    }

    /**
     * Deletes the directory containing the downloaded files.
     */
    protected function emptyAuthenticationFileDirectory()
    {
        $this->getContainer()->get('filesystem')->remove($this->authenticationDirectory);
    }

    /**
     * Downloads and stores files with authenticatable ip addresses.
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
            'nllicencemodel_export?lmuid='.$key['key'].'&wfstate=authorized&mtype=text/csv',
            [
                'cookies' => $jar,
            ]
        )->getBody()->__toString();

            $fs = $this->getContainer()->get('filesystem');
            $target = $this->authenticationDirectory.$key['title'].'.csv';

            $fs->dumpFile($target, $csv);
        }
    }

    protected function getIps()
    {
        $finder = new Finder();
        $files = $finder->in($this->authenticationDirectory);

        $ipv4Ips = [];
        foreach ($files as $file) {
            $product = explode('.', $file->getFilename())[0];
            $reader = Reader::createFromPath($file);
            $reader->setDelimiter(';');
            $keys = ['user_name', 'status', 'title', 'street', 'zip', 'city', 'county', 'country', 'telephone', 'fax', 'email', 'url', 'contactperson', 'sigel', 'ezb_id', 'subscriper_group', 'ipv4_allow', 'ipv4_deny', 'zuid', 'mtime', 'mtime_license', 'status_license', 'mtime_status'];

            $results = $reader->fetchAssoc($keys);

            foreach ($results as $key => $row) {
                if ($key > 0) {
                    $ipv4Arr = explode(',', $row['ipv4_allow']);
                    foreach ($ipv4Arr as $ipv4) {
                        $ipv4Ips[] = [$product, $row['title'], $ipv4];
                    }
                }
            }
        }

        $thirdPartRanges = [];
        $fourthPartRanges = [];
        foreach ($ipv4Ips as $k => $ipv4Ip) {
            if (strchr($ipv4Ip[2], '*')) {
                $ipv4Ip[2] = str_replace('*', '0-255', $ipv4Ip[2]);
            }

            $ipParts = explode('.', $ipv4Ip[2]);

            $ipFirstPart = $ipParts[0];
            $ipSecondPart = $ipParts[1];
            $ipThirdPart = $ipParts[2];
            $ipFourthPart = $ipParts[3];

            if (strchr($ipThirdPart, '-')) {
                $thirdPartRanges = explode('-', $ipThirdPart);
            } else {
                $ipThirdPart = $ipParts[2];
            }

            if (strchr($ipFourthPart, '-')) {
                $fourthPartRanges = explode('-', $ipFourthPart);
            } else {
                $ipFourthPart = $ipParts[3];
            }

            $startIp = $ipFirstPart.'.'.$ipSecondPart;
            if (is_array($thirdPartRanges) && $thirdPartRanges != []) {
                $startIp .= '.'.$thirdPartRanges[0];
            } else {
                $startIp .= '.'.$ipThirdPart;
            }
            if (is_array($fourthPartRanges) && $fourthPartRanges != []) {
                $startIp .= '.'.$fourthPartRanges[0];
            } else {
                $startIp .= '.'.$ipFourthPart;
            }

            $endIp = $ipFirstPart.'.'.$ipSecondPart;
            if (is_array($thirdPartRanges) && $thirdPartRanges != []) {
                $endIp .= '.'.$thirdPartRanges[1];
            } else {
                $endIp .= '.'.$ipThirdPart;
            }
            if (is_array($fourthPartRanges) && $fourthPartRanges != []) {
                $endIp .= '.'.$fourthPartRanges[1];
            } else {
                $endIp .= '.'.$ipFourthPart;
            }

            $thirdPartRanges = [];
            $fourthPartRanges = [];

            $this->addUser(ip2long($startIp), ip2long($endIp), $ipv4Ip[1], $ipv4Ip[0]);
        }
    }

    /**
     * @param string $startIpAddress
     * @param string $endIpAddress
     * @param string $institution
     * @param string $product
     */
    protected function addUser($startIpAddress, $endIpAddress, $institution, $product)
    {
        $user = new User();
        $user
            ->setStartIpAddress($startIpAddress)
            ->setEndIpAddress($endIpAddress)
            ->setInstitution($institution)
            ->setProduct($product);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
