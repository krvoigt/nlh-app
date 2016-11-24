<?php

namespace AppBundle\Service;

use Solarium\Client;
use Solarium\QueryType\Select\Result\Document;

/**
 * Document related service.
 */
class DocumentService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * DocumentService constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $id
     *
     * @return Document
     */
    public function getDocumentById($id)
    {
        if (strchr($id, '|')) {
            $id = explode('|', $id)[0];
        }
        $select = $this->client->createSelect();
        $select->setQuery('id:'.$id);
        $document = $this->client->select($select);
        $document = $document->getDocuments();
        if (count($document) === 0) {
            throw new \InvalidArgumentException(sprintf('Document %s not found', $id));
        }

        return $document[0];
    }

    /*
     * Returns the already indexed products from solr server
     * @return array
     */
    public function getAvailableProducts()
    {
        $query = $this->client->createSelect();
        $query->setFields(['product']);
        $query->addParam('group', true);
        $query->addParam('group.field', 'product');
        $query->addParam('group.main', true);
        $resultset = $this->client->select($query)->getDocuments();
        $products = array_column($resultset, 'product');

        return sort($products);
    }

    /*
     * This returns the chapter id for a given page
     *
     * @param array $chapterArr The chapter array
     * @param integer $page The page number
     *
     * @return string $chapterId The chapter id
     */
    public function getChapterId(array $chapterArr, int $page)
    {
        foreach ($chapterArr as $chapter) {
            $chapterFirstPage = ltrim(explode('_', $chapter['chapterFirstPage'])[1], 0);
            $chapterLastPage = ltrim(explode('_', $chapter['chapterLastPage'])[1], 0);

            if (in_array($page, range($chapterFirstPage, $chapterLastPage))) {
                $chapterId = $chapter['chapterId'];

                return $chapterId;
            }
        }

        return false;
    }

    /*
     * This flattens the document structure for navigation
     * @TODO Check typehint - array? Object?
     * @param mixed $structure The document structure
     *
     * @return array $chapterArr The flattened chapter structure
     */
    public function flattenStructure($structure)
    {
        $chapterArr = [];
        foreach ($structure as $chapter) {
            if (count($chapter->getChildren()) > 0) {
                $chapterArr = array_merge($chapterArr, $this->flattenStructure($chapter->getChildren()));
            } else {
                $chapterArr[] = ['chapterId' => $chapter->getId(),
                        'chapterFirstPage' => $chapter->getPhysicalPages()[0],
                        'chapterLastPage' => $chapter->getPhysicalPages()[count($chapter->getPhysicalPages()) - 1],
                ];
            }
        }

        return $chapterArr;
    }
}
