<?php

namespace AppBundle\Service;

use AppBundle\Entity\TableOfContents;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Mets processing and manipulation.
 */
class MetsService
{
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
            ->each(function (Crawler $node, $i) {

                $toc = $this->getTocElement($node);

                if ($node->children()->count() > 0) {
                    $children = new \SplObjectStorage();

                    $node->children()
                        ->each(function (Crawler $childNode) use (&$children) {

                            $childToc = $this->getTocElement($childNode);
                            $children->attach($childToc);
                        });
                    $toc->setChildren($children);
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
    protected function getTocElement($node)
    {
        $toc = new TableOfContents();

        $toc->setId($node->attr('ID'));
        $toc->setType($node->attr('TYPE'));
        $toc->setDmdid($node->attr('DMDID'));
        $toc->setLabel($node->attr('LABEL'));

        return $toc;
    }
}
