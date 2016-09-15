<?php

namespace AppBundle\Entity;

/**
 * Entity for document structure.
 */
class DocumentStructure
{
    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $pageCount;

    /**
     * @var int
     */
    protected $tableOfContents;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $firstChapter;

    /**
     * @var string
     */
    protected $lastChapter;

    /**
     * @var int
     */
    protected $documentFirstPage;

    /**
     * @var int
     */
    protected $documentLastPage;

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * @param int $pageCount
     */
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @return bool
     */
    public function getTableOfContents()
    {
        return $this->tableOfContents;
    }

    /**
     * @param bool $tableOfContents
     */
    public function setTableOfContents($tableOfContents)
    {
        $this->tableOfContents = $tableOfContents;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getFirstChapter()
    {
        return $this->firstChapter;
    }

    /**
     * @param string $firstChapter
     */
    public function setFirstChapter($firstChapter)
    {
        $this->firstChapter = $firstChapter;
    }

    /**
     * @return string
     */
    public function getLastChapter()
    {
        return $this->lastChapter;
    }

    /**
     * @param string $lastChapter
     */
    public function setLastChapter($lastChapter)
    {
        $this->lastChapter = $lastChapter;
    }

    /**
     * @return int
     */
    public function getDocumentFirstPage()
    {
        return $this->documentFirstPage;
    }

    /**
     * @param int $documentFirstPage
     */
    public function setDocumentFirstPage($documentFirstPage)
    {
        $this->documentFirstPage = $documentFirstPage;
    }

    /**
     * @return int
     */
    public function getDocumentLastPage()
    {
        return $this->documentLastPage;
    }

    /**
     * @param int $documentLastPage
     */
    public function setDocumentLastPage($documentLastPage)
    {
        $this->documentLastPage = $documentLastPage;
    }
}
