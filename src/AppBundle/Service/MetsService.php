<?php

namespace AppBundle\Service;

use AppBundle\Model\TableOfContents;
use GuzzleHttp\Exception\ClientException;
use League\Flysystem\Filesystem;
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
     * @var Filesystem
     */
    protected $metsClient;

    /**
     * MetsService constructor.
     *
     * @param \Solarium\Client $solrService
     * @param Filesystem       $metsClient
     */
    public function __construct(\Solarium\Client $solrService, Filesystem $metsClient)
    {
        $this->solrService = $solrService;
        $this->metsClient = $metsClient;
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected function getMetsFile($id)
    {
        $client = $this->solrService;
        $select = $client->createSelect()->setQuery(sprintf('id:%s', $id));
        $document = $client->select($select)->getDocuments()[0];

        $identifier = $document->presentation_url[0];
        $identifier = str_replace('https://nl.sub.uni-goettingen.de/image/', '', $identifier);
        $identifier = str_replace('/full/full/0/default.jpg', '', $identifier);
        $identifierParts = explode(':', $identifier);

        $file = $this->metsClient->read(vsprintf('/mets/%s/%s.mets.xml', [$identifierParts[0], $identifierParts[1]]));

        return $file;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getTableOfContents($id)
    {
        $storage = [];

        try {
            $metsFile = $this->getMetsFile($id);
        } catch (ClientException $e) {
            $id = $this->getParentDocument($id);
            try {
                $metsFile = $this->getMetsFile($id);
            } catch (ClientException $e) {
                $id = $this->getParentDocument($id);
                $metsFile = $this->getMetsFile($id);
            }
        }

        $query = '//mets:mets/mets:structMap[@TYPE="LOGICAL"]/mets:div';

        $crawler = new Crawler();
        $crawler->addContent($metsFile);

        $pageMappings = $this->getPageMapping($crawler);

        $storage[] = $crawler->filterXPath($query)->children()->each(function (Crawler $node) use ($pageMappings) {
            $toc = [];
            if ($node->getNode(0)->tagName !== 'mets:mptr') {
                $toc = $this->getTocElement($node, $pageMappings);
                $children = new \SplObjectStorage();
                $node->children()->each(
                          function (Crawler $childNode) use (&$children, &$toc, $pageMappings) {
                              $childToc = $this->getTocElement($childNode, $pageMappings);
                              $children = new \SplObjectStorage();
                              $toc->addChildren($childToc);
                              $childNode->children()->each(
                                  function (Crawler $child_1Node) use (&$children, &$childToc, $pageMappings) {
                                      $child_1Toc = $this->getTocElement($child_1Node, $pageMappings);
                                      $children = new \SplObjectStorage();
                                      $childToc->addChildren($child_1Toc);
                                      $child_1Node->children()->each(function (Crawler $child_2Node) use (&$children, &$child_1Toc, $pageMappings) {
                                          $child_2Toc = $this->getTocElement($child_2Node, $pageMappings);
                                          $children = new \SplObjectStorage();
                                          $child_1Toc->addChildren($child_2Toc);
                                          $child_2Node->children()->each(function (Crawler $child_3Node) use (&$children, &$child_2Toc, $pageMappings) {
                                              $child_3Toc = $this->getTocElement($child_3Node, $pageMappings);
                                              $children = new \SplObjectStorage();
                                              $child_2Toc->addChildren($child_3Toc);
                                          });
                                      });
                                  });
                          });
            }

            return $toc;
        });

        if (count($storage) === 0) {
            self::getTableOfContents($this->getParentDocument($id));
        }

        return $storage;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getScannedPagesMapping($id)
    {
        try {
            $metsFile = $this->getMetsFile($id);
        } catch (ClientException $e) {
            $id = $this->getParentDocument($id);

            try {
                $metsFile = $this->getMetsFile($id);
            } catch (ClientException $e) {
                $id = $this->getParentDocument($id);
                $metsFile = $this->getMetsFile($id);
            }
        }

        $links = [];

        $crawler = new Crawler();
        $crawler->addContent($metsFile);

        $crawler->filterXPath('//mets:mets/mets:structMap[@TYPE="PHYSICAL"]/mets:div')->children()->each(function (Crawler $node) use (&$links) {
            $key = $node->attr('ORDER');

            $links[$key] = '';

            if ($node->attr('ORDERLABEL')) {
                $links[$key] = $node->attr('ORDERLABEL');
            }
        });

        return $links;
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
    protected function getTocElement(Crawler $node, $linkSegment)
    {
        $toc = new TableOfContents();

        $toc->setId($node->attr('ID'));
        $toc->setType($node->attr('TYPE'));
        $toc->setDmdid($node->attr('DMDID'));
        $toc->setLabel($node->attr('LABEL'));

        if (isset($linkSegment[$node->attr('ID')])) {
            $toc->setPhysicalPages($linkSegment[$node->attr('ID')]);
        } else {
            $toc->setPhysicalPages([]);
        }

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
