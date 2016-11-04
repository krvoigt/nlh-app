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

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $id
     *
     * @return Document
     */
    public function getDocumentById($id)
    {
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
}
