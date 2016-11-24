<?php

namespace AppBundle\Model;

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
     * @var bool
     */
    protected $isValidPage;

    /**
     * @var bool
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
     * @var bool
     */
    protected $isThereAPreviousChapter;

    /**
     * @var bool
     */
    protected $isThereANextChapter;

    /**
     * @var string
     */
    protected $previousChapterId;

    /**
     * @var int
     */
    protected $previousChapterFirstPage;

    /**
     * @var string
     */
    protected $nextChapterId;

    /**
     * @var int
     */
    protected $nextChapterFirstPage;

    /**
     * @var string
     */
    protected $nextPageChapterId;

    /**
     * @var string
     */
    protected $previousPageChapterId;

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return DocumentStructure
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValidPage()
    {
        return $this->isValidPage;
    }

    /**
     * @param bool $isValidPage
     *
     * @return DocumentStructure
     */
    public function setIsValidPage($isValidPage)
    {
        $this->isValidPage = $isValidPage;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setTableOfContents($tableOfContents)
    {
        $this->tableOfContents = $tableOfContents;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setFirstChapter($firstChapter)
    {
        $this->firstChapter = $firstChapter;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setLastChapter($lastChapter)
    {
        $this->lastChapter = $lastChapter;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setDocumentFirstPage($documentFirstPage)
    {
        $this->documentFirstPage = $documentFirstPage;

        return $this;
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
     *
     * @return DocumentStructure
     */
    public function setDocumentLastPage($documentLastPage)
    {
        $this->documentLastPage = $documentLastPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsThereAPreviousChapter()
    {
        return $this->isThereAPreviousChapter;
    }

    /**
     * @param bool $isThereAPreviousChapter
     *
     * @return DocumentStructure
     */
    public function setIsThereAPreviousChapter($isThereAPreviousChapter)
    {
        $this->isThereAPreviousChapter = $isThereAPreviousChapter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsThereANextChapter()
    {
        return $this->isThereANextChapter;
    }

    /**
     * @param bool $isThereANextChapter
     *
     * @return DocumentStructure
     */
    public function setIsThereANextChapter($isThereANextChapter)
    {
        $this->isThereANextChapter = $isThereANextChapter;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreviousChapterId()
    {
        return $this->previousChapterId;
    }

    /**
     * @param int $previousChapterId
     *
     * @return DocumentStructure
     */
    public function setPreviousChapterId($previousChapterId)
    {
        $this->previousChapterId = $previousChapterId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPreviousChapterFirstPage()
    {
        return $this->previousChapterFirstPage;
    }

    /**
     * @param int $previousChapterFirstPage
     *
     * @return DocumentStructure
     */
    public function setPreviousChapterFirstPage($previousChapterFirstPage)
    {
        $this->previousChapterFirstPage = $previousChapterFirstPage;

        return $this;
    }

    /**
     * @return string
     */
    public function getNextChapterId()
    {
        return $this->nextChapterId;
    }

    /**
     * @param int $nextChapterId
     *
     * @return DocumentStructure
     */
    public function setNextChapterId($nextChapterId)
    {
        $this->nextChapterId = $nextChapterId;

        return $this;
    }

    /**
     * @return int
     */
    public function getNextChapterFirstPage()
    {
        return $this->nextChapterFirstPage;
    }

    /**
     * @param int $nextChapterFirstPage
     *
     * @return DocumentStructure
     */
    public function setNextChapterFirstPage($nextChapterFirstPage)
    {
        $this->nextChapterFirstPage = $nextChapterFirstPage;

        return $this;
    }

    /**
     * @return string
     */
    public function getNextPageChapterId()
    {
        return $this->nextPageChapterId;
    }

    /**
     * @param int $nextPageChapterId
     *
     * @return DocumentStructure
     */
    public function setNextPageChapterId($nextPageChapterId)
    {
        $this->nextPageChapterId = $nextPageChapterId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreviousPageChapterId()
    {
        return $this->previousPageChapterId;
    }

    /**
     * @param int $previousPageChapterId
     *
     * @return DocumentStructure
     */
    public function setPreviousPageChapterId($previousPageChapterId)
    {
        $this->previousPageChapterId = $previousPageChapterId;

        return $this;
    }
}
