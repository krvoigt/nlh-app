<?php

namespace tests\AppBundle\Service;

use AppBundle\Model\TableOfContents;
use AppBundle\Service\MetsService;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Response;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Finder\Finder;

class MetsServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetsService
     */
    protected $fixture;

    public function setUp()
    {
        $solariumMock = $this->getMockBuilder(\Solarium\Client::class)->getMock();

        $streamInterfaceMock = $this->getMockBuilder(BufferStream::class)
            ->setMethods(['__toString'])
            ->getMock();

        $responseInterfaceMock = $this
            ->getMockBuilder(Response::class)
            ->setMethods(['getBody'])
            ->getMock();

        $cacheMock = $this
            ->getMockBuilder(NullAdapter::class)
            ->setMethods(['isHit'])
            ->getMock();

        $cacheMock
            ->expects($this->any())
            ->method('isHit')
            ->willReturn('false');

        $adapterInterfaceMock = $this
                    ->getMockBuilder(AdapterInterface::class)
                    ->getMock();

        $metsClientMock = $this
            ->getMockBuilder(Filesystem::class)
            ->setConstructorArgs(['adapter' => $adapterInterfaceMock])
            ->disableOriginalConstructor()
            ->getMock();

        $metsClientMock
             ->expects($this->any())
             ->willReturn(
                 $responseInterfaceMock
             );

        $responseInterfaceMock
            ->expects($this->any())
            ->method('getBody')
            ->willReturn(
                $streamInterfaceMock
            );

        $streamInterfaceMock
              ->expects($this->any())
              ->method('__toString')
              ->willReturn(
                  $this->metsDataProvider()[0][0]
              );

        parent::setUp();
        $this->fixture = new MetsService($solariumMock, $metsClientMock);
    }

    /**
     * @dataProvider metsDataProvider
     */
    public function testNumberOfElements($mets)
    {
        $this->markTestSkipped('GDZ only');
        $result = $this->fixture->getTableOfContents($mets);

        $this->assertCount(15, $result[0]);
    }

    /**
     * @dataProvider metsDataProvider
     */
    public function testTypeOfObject($mets)
    {
        $this->markTestSkipped('GDZ only');
        $result = $this->fixture->getTableOfContents($mets);

        $this->assertInstanceOf(TableOfContents::class, $result[0][0]);
        $this->assertInstanceOf(TableOfContents::class, $result[0][1]);
        $this->assertInstanceOf(TableOfContents::class, $result[0][8]);
        $this->assertInstanceOf(TableOfContents::class, $result[0][9]);
        $this->assertInstanceOf(TableOfContents::class, $result[0][10]);
    }

    /**
     * @dataProvider metsDataProvider
     */
    public function testPhysicalPageLinksExist($mets)
    {
        $this->markTestSkipped('GDZ only');
        $result = $this->fixture->getTableOfContents($mets);
        $this->assertAttributeNotEmpty('physicalPages', $result[0][0]);
        $this->assertAttributeCount(14, 'physicalPages', $result[0][13]);
        $this->assertAttributeCount(2, 'physicalPages', $result[0][14]);
    }

    public function metsDataProvider()
    {
        $fs = new Finder();
        $fs->files()->in(__DIR__.'/../../Objects/Mets/')->name('PPN530582384.xml');

        $metsContainer = [];

        foreach ($fs as $mets) {
            $metsContainer [] = $mets->getContents();
        }

        $mets = [$metsContainer];

        return $mets;
    }
}
