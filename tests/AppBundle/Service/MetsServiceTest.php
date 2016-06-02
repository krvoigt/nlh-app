<?php

namespace tests\AppBundle\Service;

use AppBundle\Service\MetsService;

class MetsServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetsService
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new MetsService();
    }

    public function testMetsProcessing()
    {
        $this->markTestSkipped();
    }
}
