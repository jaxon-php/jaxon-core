<?php

namespace Jaxon\Tests\TestRegistration;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\Exception\SetupException;
use Jaxon\Tests\Ns\Annotated\Upload;
use mindplay\annotations\AnnotationManager;
use PHPUnit\Framework\TestCase;

class AnnotationTest extends TestCase
{
    /**
     * @var AnnotationManager
     */
    protected $xAnnotationReader;

    public function setUp(): void
    {
        $sCacheDir = __DIR__ . '/../upload/tmp';
        @unlink($sCacheDir);
        @mkdir($sCacheDir);
        jaxon()->di()->val('jaxon_annotations_cache_dir', $sCacheDir);
        $this->xAnnotationReader = jaxon()->di()->g(AnnotationReader::class);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testUploadAndExcludeAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getClassAttributes(Upload::class, ['saveFiles', 'doNot']);

        $aProperties = $aAttributes[0];
        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('saveFiles', $aProperties);
        $this->assertCount(1, $aProperties['saveFiles']);
        $this->assertEquals("'user-files'", $aProperties['saveFiles']['upload']);

        $aProtected = $aAttributes[1];
        $this->assertCount(1, $aProtected);
        $this->assertEquals('doNot', $aProtected[0]);
    }

    public function testDataBagAnnotation()
    {
        [$aProperties, ] = $this->xAnnotationReader->getClassAttributes(Upload::class, ['withBags']);

        $this->assertCount(1, $aProperties);
        // $this->assertEquals('', json_encode($aProperties));
        $this->assertArrayHasKey('withBags', $aProperties);
        $this->assertCount(1, $aProperties['withBags']);
        $this->assertCount(2, $aProperties['withBags']['bags']);
        $this->assertEquals('user.name', $aProperties['withBags']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['withBags']['bags'][1]);
    }
}
