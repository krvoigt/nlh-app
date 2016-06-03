<?php

namespace AppBundle\Entity;

/**
 * Entity for table of contents.
 */
class TableOfContents
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $dmdid;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var \SplObjectStorage<TableOfContents>
     */
    protected $children;

    public function __construct()
    {
        $this->children = new \SplObjectStorage();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDmdid()
    {
        return $this->dmdid;
    }

    /**
     * @param string $dmdid
     */
    public function setDmdid($dmdid)
    {
        $this->dmdid = $dmdid;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param TableOfContents $children
     */
    public function addChildren($children)
    {
        $this->children->attach($children);
    }
}
