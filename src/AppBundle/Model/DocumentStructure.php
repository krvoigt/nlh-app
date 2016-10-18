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
    public function getIsValidPage()
    {
        return $this->isValidPage;
    }

    /**
     * @param bool $isValidPage
     */
    public function setIsValidPage($isValidPage)
    {
        $this->isValidPage = $isValidPage;
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

    /**
     * @return bool
     */
    public function getIsThereAPreviousChapter()
    {
        return $this->isThereAPreviousChapter;
    }

    /**
     * @param bool $isThereAPreviousChapter
     */
    public function setIsThereAPreviousChapter($isThereAPreviousChapter)
    {
        $this->isThereAPreviousChapter = $isThereAPreviousChapter;
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
     */
    public function setIsThereANextChapter($isThereANextChapter)
    {
        $this->isThereANextChapter = $isThereANextChapter;
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
     */
    public function setPreviousChapterId($previousChapterId)
    {
        $this->previousChapterId = $previousChapterId;
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
     */
    public function setPreviousChapterFirstPage($previousChapterFirstPage)
    {
        $this->previousChapterFirstPage = $previousChapterFirstPage;
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
     */
    public function setNextChapterId($nextChapterId)
    {
        $this->nextChapterId = $nextChapterId;
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
     */
    public function setNextChapterFirstPage($nextChapterFirstPage)
    {
        $this->nextChapterFirstPage = $nextChapterFirstPage;
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
     */
    public function setNextPageChapterId($nextPageChapterId)
    {
        $this->nextPageChapterId = $nextPageChapterId;
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
     */
    public function setPreviousPageChapterId($previousPageChapterId)
    {
        $this->previousPageChapterId = $previousPageChapterId;
    }
}
