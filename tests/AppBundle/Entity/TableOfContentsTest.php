<?php

declare(strict_types=1);

namespace tests\AppBundle\Service;

use AppBundle\Entity\TableOfContents;

class TableOfContentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableOfContents
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new TableOfContents();
    }

    public function testSettingTheLabelWillReturnTheCorrectLabel()
    {
        $label = 'Titel';
        $this->fixture->setLabel($label);

        $this->assertSame($label, $this->fixture->getLabel());
    }

    public function testSettingTheIdWillReturnTheCorrectId()
    {
        $label = 'PPN888';
        $this->fixture->setId($label);

        $this->assertSame($label, $this->fixture->getId());
    }

    public function testSettingTheTypeWillReturnTheCorrectType()
    {
        $type = 'Type';
        $this->fixture->setType($type);

        $this->assertSame($type, $this->fixture->getType());
    }

    public function testSettingThDmdidWillReturnTheCorrectDmdid()
    {
        $dmdid = 'dmdid';
        $this->fixture->setDmdid($dmdid);

        $this->assertSame($dmdid, $this->fixture->getDmdid());
    }

    public function testIfAddingChildrenWillReallyAddThem()
    {
        $toc1 = new TableOfContents();
        $toc2 = new TableOfContents();
        $toc3 = new TableOfContents();

        $this->fixture->addChildren($toc1);
        $this->fixture->addChildren($toc2);
        $this->fixture->addChildren($toc3);

        $this->assertCount(3, $this->fixture->getChildren());
    }
}
