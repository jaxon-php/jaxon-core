<?php

namespace Jaxon\Tests\TestRegistration;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\Exception\SetupException;
use Jaxon\Tests\Ns\Annotated\Annotated;
use Jaxon\Tests\Ns\Annotated\ClassAnnotated;
use Jaxon\Tests\Ns\Annotated\ClassExcluded;
use mindplay\annotations\AnnotationException;
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
        [$bExcluded, $aProperties, $aProtected] = $this->xAnnotationReader->getAttributes(Annotated::class, ['saveFiles', 'doNot']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('saveFiles', $aProperties);
        $this->assertCount(1, $aProperties['saveFiles']);
        $this->assertEquals("'user-files'", $aProperties['saveFiles']['upload']);

        $this->assertCount(1, $aProtected);
        $this->assertEquals('doNot', $aProtected[0]);
    }

    public function testDataBagAnnotation()
    {
        [$bExcluded, $aProperties, ] = $this->xAnnotationReader->getAttributes(Annotated::class, ['withBags']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('withBags', $aProperties);
        $this->assertCount(1, $aProperties['withBags']);
        $this->assertCount(2, $aProperties['withBags']['bags']);
        $this->assertEquals('user.name', $aProperties['withBags']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['withBags']['bags'][1]);
    }

    public function testCallbacksAnnotation()
    {
        [$bExcluded, $aProperties, ] = $this->xAnnotationReader->getAttributes(Annotated::class,
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

    public function testClassAnnotation()
    {
        [$bExcluded, $aProperties, $aProtected] = $this->xAnnotationReader->getAttributes(ClassAnnotated::class, []);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']);
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
    }

    public function testClassExcludeAnnotation()
    {
        [$bExcluded, $aProperties, $aProtected] = $this->xAnnotationReader->getAttributes(ClassExcluded::class,
            ['doNot', 'withBags', 'cbSingle']);

        $this->assertTrue($bExcluded);
        $this->assertEmpty($aProperties);
        $this->assertEmpty($aProtected);
    }

    public function testExcludeAnnotationError()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['doNotError']);
    }

    public function testDataBagAnnotationError()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['withBagsError']);
    }

    public function testUploadAnnotationWrongName()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['saveFilesWrongName']);
    }

    public function testUploadAnnotationMultiple()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['saveFilesMultiple']);
    }

    public function testCallbacksBeforeAnnotationNoCall()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['cbBeforeNoCall']);
    }

    public function testCallbacksBeforeAnnotationUnknownAttr()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['cbBeforeUnknownAttr']);
    }

    public function testCallbacksBeforeAnnotationWrongAttrType()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['cbBeforeWrongAttrType']);
    }

    public function testCallbacksAfterAnnotationNoCall()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['cbAfterNoCall']);
    }

    public function testCallbacksAfterAnnotationUnknownAttr()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['cbAfterUnknownAttr']);
    }

    public function testCallbacksAfterAnnotationWrongAttrType()
    {
        $this->expectException(AnnotationException::class);
        $this->xAnnotationReader->getAttributes(Annotated::class, ['cbAfterWrongAttrType']);
    }
}
