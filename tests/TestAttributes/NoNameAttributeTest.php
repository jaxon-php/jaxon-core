<?php

namespace Jaxon\Tests\TestAttributes;

use Jaxon\Plugin\Attribute\AttributeReader;
use Jaxon\Tests\App\Attr\Ajax\AttributeNoName;
use Jaxon\Tests\App\Attr\Ajax\ClassAttributeNoName;
use Jaxon\Tests\App\Attr\Ajax\ClassExcludedNoName;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function mkdir;
use function rmdir;

class NoNameAttributeTest extends TestCase
{
    /**
     * @var string
     */
    private $sCacheDir;

    /**
     * @var AttributeReader
     */
    protected $xAttributeReader;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        $this->sCacheDir = __DIR__ . '/../cache';
        @mkdir($this->sCacheDir);

        jaxon()->setOption('core.attributes.enabled', true);
        jaxon()->di()->val('jaxon_attributes_cache_dir', $this->sCacheDir);
        $this->xAttributeReader = jaxon()->di()->g(AttributeReader::class);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();

        // Delete the temp dir and all its content
        $aFiles = scandir($this->sCacheDir);
        foreach ($aFiles as $sFile)
        {
            if($sFile !== '.' && $sFile !== '..')
            {
                @unlink($this->sCacheDir . DIRECTORY_SEPARATOR . $sFile);
            }
        }
        @rmdir($this->sCacheDir);
    }

    /**
     * @throws SetupException
     */
    public function testUploadAndExcludeAttribute()
    {
        [$bExcluded, $aProperties, $aProtected] = $this->xAttributeReader->getAttributes(AttributeNoName::class, ['saveFiles', 'doNot']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('saveFiles', $aProperties);
        $this->assertCount(1, $aProperties['saveFiles']);
        $this->assertEquals("'user-files'", $aProperties['saveFiles']['upload']);

        $this->assertCount(1, $aProtected);
        $this->assertEquals('doNot', $aProtected[0]);
    }

    /**
     * @throws SetupException
     */
    public function testDataBagAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(AttributeNoName::class, ['withBags']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('withBags', $aProperties);
        $this->assertCount(1, $aProperties['withBags']);
        $this->assertCount(2, $aProperties['withBags']['bags']);
        $this->assertEquals('user.name', $aProperties['withBags']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['withBags']['bags'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testCallbacksAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(AttributeNoName::class,
            ['cbSingle', 'cbMultiple', 'cbParams']);

        $this->assertFalse($bExcluded);

        $this->assertCount(3, $aProperties);
        $this->assertArrayHasKey('cbSingle', $aProperties);
        $this->assertArrayHasKey('cbMultiple', $aProperties);
        $this->assertArrayHasKey('cbParams', $aProperties);

        $this->assertCount(1, $aProperties['cbSingle']['__before']);
        $this->assertCount(2, $aProperties['cbMultiple']['__before']);
        $this->assertCount(2, $aProperties['cbParams']['__before']);
        $this->assertArrayHasKey('funcBefore', $aProperties['cbSingle']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['cbMultiple']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['cbMultiple']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['cbParams']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['cbParams']['__before']);
        $this->assertIsArray($aProperties['cbSingle']['__before']['funcBefore']);
        $this->assertIsArray($aProperties['cbMultiple']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['cbMultiple']['__before']['funcBefore2']);
        $this->assertIsArray($aProperties['cbParams']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['cbParams']['__before']['funcBefore2']);

        $this->assertCount(1, $aProperties['cbSingle']['__after']);
        $this->assertCount(3, $aProperties['cbMultiple']['__after']);
        $this->assertCount(1, $aProperties['cbParams']['__after']);
        $this->assertArrayHasKey('funcAfter', $aProperties['cbSingle']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['cbMultiple']['__after']);
        $this->assertArrayHasKey('funcAfter2', $aProperties['cbMultiple']['__after']);
        $this->assertArrayHasKey('funcAfter3', $aProperties['cbMultiple']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['cbParams']['__after']);
        $this->assertIsArray($aProperties['cbSingle']['__after']['funcAfter']);
        $this->assertIsArray($aProperties['cbMultiple']['__after']['funcAfter1']);
        $this->assertIsArray($aProperties['cbMultiple']['__after']['funcAfter2']);
        $this->assertIsArray($aProperties['cbMultiple']['__after']['funcAfter3']);
        $this->assertIsArray($aProperties['cbParams']['__after']['funcAfter1']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(AttributeNoName::class, ['di1', 'di2']);

        $this->assertFalse($bExcluded);

        $this->assertCount(2, $aProperties);
        $this->assertArrayHasKey('di1', $aProperties);
        $this->assertArrayHasKey('di2', $aProperties);
        $this->assertCount(2, $aProperties['di1']['__di']);
        $this->assertCount(2, $aProperties['di2']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['di1']['__di']['colorService']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['di1']['__di']['fontService']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['di2']['__di']['colorService']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['di2']['__di']['textService']);
    }

    /**
     * @throws SetupException
     */
    public function testClassAttribute()
    {
        [$bExcluded, $aProperties,] = $this->xAttributeReader->getAttributes(ClassAttributeNoName::class, []);
        // $this->assertEquals('', json_encode($aProperties));

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(4, $aProperties['*']);
        $this->assertArrayHasKey('bags', $aProperties['*']);
        $this->assertArrayHasKey('__before', $aProperties['*']);
        $this->assertArrayHasKey('__after', $aProperties['*']);

        $this->assertCount(2, $aProperties['*']['bags']);
        $this->assertEquals('user.name', $aProperties['*']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['*']['bags'][1]);

        $this->assertCount(2, $aProperties['*']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['*']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['*']['__before']);
        $this->assertIsArray($aProperties['*']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['*']['__before']['funcBefore2']);

        $this->assertCount(3, $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter2', $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter3', $aProperties['*']['__after']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter1']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter2']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter3']);

        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertArrayHasKey('colorService', $aProperties['*']['__di']);
        $this->assertArrayHasKey('textService', $aProperties['*']['__di']);
        $this->assertArrayHasKey('fontService', $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['*']['__di']['colorService']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['*']['__di']['textService']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['*']['__di']['fontService']);
    }

    /**
     * @throws SetupException
     */
    public function testClassExcludeAttribute()
    {
        [$bExcluded, $aProperties, $aProtected] = $this->xAttributeReader->getAttributes(ClassExcludedNoName::class,
            ['doNot', 'withBags', 'cbSingle']);

        $this->assertTrue($bExcluded);
        $this->assertEmpty($aProperties);
        $this->assertEmpty($aProtected);
    }

    public function testUploadAttributeErrorFieldName()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['saveFileErrorFieldName']);
    }

    public function testUploadAttributeErrorFieldNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['saveFileErrorFieldNumber']);
    }

    public function testDataBagAttributeErrorName()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['withBagsErrorName']);
    }

    public function testDataBagAttributeErrorNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['withBagsErrorNumber']);
    }

    public function testContainerAttributeErrorAttr()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['diErrorAttr']);
    }

    public function testContainerAttributeErrorClass()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['diErrorClass']);
    }

    public function testContainerAttributeErrorOneParam()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['diErrorOneParam']);
    }

    public function testContainerAttributeErrorThreeParams()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['diErrorThreeParams']);
    }

    public function testCbBeforeAttributeErrorName()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['cbBeforeErrorName']);
    }

    public function testCbBeforeAttributeErrorParam()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['cbBeforeErrorParam']);
    }

    public function testCbBeforeAttributeErrorNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['cbBeforeErrorNumber']);
    }

    public function testCbAfterAttributeErrorName()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['cbAfterErrorName']);
    }

    public function testCbAfterAttributeErrorParam()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['cbAfterErrorParam']);
    }

    public function testCbAfterAttributeErrorNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(AttributeNoName::class, ['cbAfterErrorNumber']);
    }
}
