<?php

namespace AppBundle\Service;

use AppBundle\Entity\TableOfContents;
use GuzzleHttp\Client;
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

    public function __construct(\Solarium\Client $solrService, AdapterInterface $cache, Client $metsClient)
    {
        $this->solrService = $solrService;
        $this->cache = $cache;
        $this->metsClient = $metsClient;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getTableOfContents($id)
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

        $crawler = new Crawler();
        $crawler->addContent($metsFile->get());

        $storage = [];

        $storage[] = $crawler
            ->filterXPath('//mets:mets/mets:structMap/mets:div')
            ->children()
            ->each(function (Crawler $node) {

                $toc = $this->getTocElement($node);

                if ($node->children()->count() > 0) {
                    $children = new \SplObjectStorage();

                    $node->children()
                        ->each(function (Crawler $childNode) use (&$children, &$toc) {

                            $childToc = $this->getTocElement($childNode);
                            $toc->addChildren($childToc);
                        });
                }

                return $toc;
            });

        return $storage;
    }

    /**
     * @param Crawler $node
     *
     * @return TableOfContents
     */
    protected function getTocElement(Crawler $node)
    {
        $toc = new TableOfContents();

        $toc->setId($node->attr('ID'));
        $toc->setType($node->attr('TYPE'));
        $toc->setDmdid($node->attr('DMDID'));
        $toc->setLabel($node->attr('LABEL'));

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
            $id = array_pop($document['idparentdoc']);
        }

        return $id;
    }
}
