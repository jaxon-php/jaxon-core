<?php

namespace Jaxon\Tests\TestAttributes;

use Jaxon\Plugin\Attribute\AttributeReader;
use Jaxon\Tests\App\Attr\Ajax\PropertyAttribute;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;

class PropertyAttributeTest extends TestCase
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

        jaxon()->di()->val('jaxon_attributes_cache_dir', $this->sCacheDir);
        $this->xAttributeReader = jaxon()->di()->getMetadataReader('attributes');
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
    public function testContainerAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(PropertyAttribute::class,
            ['attrVar'], ['colorService', 'fontService', 'textService']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('attrVar', $aProperties);
        $this->assertCount(3, $aProperties['attrVar']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['attrVar']['__di']['colorService']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['attrVar']['__di']['fontService']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['attrVar']['__di']['textService']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDocBlockAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(PropertyAttribute::class,
            ['attrDbVar'], ['colorService', 'fontService', 'textService']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('attrDbVar', $aProperties);
        $this->assertCount(3, $aProperties['attrDbVar']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['attrDbVar']['__di']['colorService']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['attrDbVar']['__di']['fontService']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['attrDbVar']['__di']['textService']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDiAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(PropertyAttribute::class,
            ['attrDi'], ['colorService1', 'fontService1', 'textService1']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['*']['__di']['colorService1']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['*']['__di']['fontService1']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['*']['__di']['textService1']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDiAndVarAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(PropertyAttribute::class,
            ['attrDi'], ['colorService2', 'fontService2', 'textService2']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['*']['__di']['colorService2']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['*']['__di']['fontService2']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['*']['__di']['textService2']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerPropAttribute()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(PropertyAttribute::class,
            ['attrDi'], ['colorService3', 'fontService3', 'textService3']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\ColorService', $aProperties['*']['__di']['colorService3']);
        $this->assertEquals('Jaxon\Tests\App\Attr\Ajax\FontService', $aProperties['*']['__di']['fontService3']);
        $this->assertEquals('Jaxon\Tests\Service\TextService', $aProperties['*']['__di']['textService3']);
    }

    public function testContainerAttributeErrorTwoParams()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(PropertyAttribute::class, [], ['errorTwoParams']);
    }

    public function testContainerAttributeErrorDiAttr()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(PropertyAttribute::class, [], ['errorDiAttr']);
    }

    public function testContainerAttributeErrorTwoDi()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(PropertyAttribute::class, [], ['errorTwoDi']);
    }

    public function testContainerAttributeErrorDiClass()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(PropertyAttribute::class, ['errorDiClass']);
    }

    public function testContainerAttributeErrorNoVar()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(PropertyAttribute::class, ['errorDiNoVar']);
    }

    public function testContainerAttributeErrorTwoVars()
    {
        $this->expectException(SetupException::class);
        $this->xAttributeReader->getAttributes(PropertyAttribute::class, ['errorDiTwoVars']);
    }
}
