<?php

namespace AppBundle\Service;

use AppBundle\Entity\TableOfContents;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Mets processing and manipulation.
 */
class MetsService
{
    protected $solrService;

    public function __construct(\Solarium\Client $solrService)
    {
        $this->solrService = $solrService;
    }

    /**
     * @param string $mets
     *
     * @return array
     */
    public function getTableOfContents($mets)
    {
        $crawler = new Crawler();
        $crawler->addContent($mets);

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

    public function getParentDocument($id)
    {
        $select = $this->solrService->createSelect();
        $select->setQuery('id:'.$id);
        $documents = $this->solrService->select($select)->getDocuments();
        $document = $documents[0]->getFields();

        if (isset($document['idparentdoc'])) {
            $id = array_pop($document['idparentdoc']);
        }

        return $id;
    }
}
