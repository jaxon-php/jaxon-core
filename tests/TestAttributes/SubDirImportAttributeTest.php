<?php
declare(strict_types=1);

namespace Jaxon\Tests\TestAttributes;

use Jaxon\Plugin\Attribute\AttributeReader;
use Jaxon\Tests\App\Attr\Ajax\SubDirImportAttribute;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;

class SubDirImportAttributeTest extends TestCase
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
    }

    public function testCbBeforeAttributeErrorNumber()
    {
        [$bExcluded, $aProperties, ] = $this->xAttributeReader->getAttributes(SubDirImportAttribute::class, ['attrDi'], ['secondService']);

        $this->assertFalse($bExcluded);

        $this->assertCount(2, $aProperties);
        $this->assertArrayHasKey('attrDi', $aProperties);
        $this->assertCount(1, $aProperties['attrDi']['__di']);
        $this->assertEquals('Jaxon\Tests\Service\SubDir\FirstService', $aProperties['attrDi']['__di']['firstService']);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertEquals('Jaxon\Tests\Service\SubDir\SecondService', $aProperties['*']['__di']['secondService']);
    }
}
