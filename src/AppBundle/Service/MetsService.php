<?php

namespace AppBundle\Service;

use AppBundle\Entity\TableOfContents;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Mets processing and manipulation.
 */
class MetsService
{
    /**
     * @var \Solarium\Client
     */
    protected $solrService;

    /**
     * @var AbstractAdapter
     */
    protected $cache;

    /**
     * @var Client
     */
    protected $metsClient;

    /**
     * MetsService constructor.
     *
     * @param \Solarium\Client $solrService
     * @param AdapterInterface $cache
     * @param Client           $metsClient
     */
    public function __construct(\Solarium\Client $solrService, AdapterInterface $cache, Client $metsClient)
    {
        $this->solrService = $solrService;
        $this->cache = $cache;
        $this->metsClient = $metsClient;
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected function getMetFile($id)
    {
        $identifier = 'mets.'.sha1($id);

        $metsFile = $this->cache->getItem($identifier);

        if (!$metsFile->isHit()) {
            $file = $this->metsClient
                ->get($id.'.xml')
                ->getBody()->__toString();

            $metsFile->set($file);
            $this->cache->save($metsFile);
        }

        return $metsFile->get();
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getTableOfContents($id)
    {
        try {
            $metsFile = $this->getMetFile($id);
        } catch (ClientException $e) {
            $id = $this->getParentDocument($id);

            try {
                $metsFile = $this->getMetFile($id);
            } catch (ClientException $e) {
                $id = $this->getParentDocument($id);
                $metsFile = $this->getMetFile($id);
            }
        }

        $crawler = new Crawler();
        $crawler->addContent($metsFile);

        $storage = [];

        $documentId = $crawler->filterXPath('//mets:mets/mets:dmdSec/mets:mdWrap/mets:xmlData/mods:mods/mods:recordInfo/mods:recordIdentifier')->text();

        $pageMapping = $this->getPageMapping($crawler);

        $storage[] = $crawler
            ->filterXPath('//mets:mets/mets:structMap/mets:div')
            ->children()
            ->each(function (Crawler $node) use ($documentId, $pageMapping) {

                $toc = $this->getTocElement($node, $documentId, $pageMapping);

                if ($node->children()->count() > 0) {
                    $children = new \SplObjectStorage();

                    $node->children()
                        ->each(function (Crawler $childNode) use (&$children, &$toc, $documentId, $pageMapping) {
                            $childToc = $this->getTocElement($childNode, $documentId, $pageMapping);
                            $toc->addChildren($childToc);
                        });
                }

                return $toc;
            });

        if (count($storage) === 0) {
            self::getTableOfContents($this->getParentDocument($id));
        };

        return $storage;
    }

    /**
     * Get the links and pages from logical to physical pages.
     *
     * @param Crawler $crawler
     *
     * @return array
     */
    protected function getPageMapping(Crawler $crawler)
    {
        $links = [];
        $crawler->filterXPath('//mets:mets/mets:structLink')->children()->each(function (Crawler $node) use (&$links) {
             $key = $node->attr('xlink:from');
             if (!array_key_exists($key, $links)) {
                 $links[$key] = [];
             }
             array_push($links[$key], $node->attr('xlink:to'));
         });

        return $links;
    }
    /**
     * @param Crawler $node
     * @param string  $parent
     * @param Crawler $linkSegment
     *
     * @return TableOfContents
     */
    protected function getTocElement(Crawler $node, $parent, $linkSegment)
    {
        $toc = new TableOfContents();

        $toc->setId($node->attr('ID'));
        $toc->setType($node->attr('TYPE'));
        $toc->setDmdid($node->attr('DMDID'));
        $toc->setLabel($node->attr('LABEL'));
        $toc->setParentDocument($parent);
        $toc->setPhysicalPages($linkSegment[$node->attr('ID')]);

        return $toc;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function getParentDocument($id)
    {
        $select = $this->solrService->createSelect();
        $select->setQuery(sprintf('id:%s', $id));
        $documents = $this->solrService->select($select)->getDocuments();
        $document = $documents[0]->getFields();

        if (isset($document['idparentdoc'])) {
            $id = $document['idparentdoc'][0];
        }

        return $id;
    }
}
