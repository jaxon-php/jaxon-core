<?php

namespace Jaxon\Tests\TestUi;

use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function trim;

class TemplateTest extends TestCase
{
    public function testTemplate()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sTemplateJs = trim(jaxon()->template()->render('plugins/include.js',
            ['sUrl' => 'http://example.test/path', 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sTemplateJs);
    }

    public function testTemplateEmbedded()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sTemplateJs = trim(jaxon()->template()->render('plugins/includes.js',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sTemplateJs);
    }

    public function testTemplateWithNamespace()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sTemplateJs = trim(jaxon()->template()->render('jaxon::plugins/include.js',
            ['sUrl' => 'http://example.test/path', 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sTemplateJs);
    }

    public function testTemplateEmbeddedWithNamespace()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sTemplateJs = trim(jaxon()->template()->render('jaxon::plugins/includes.js',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sTemplateJs);
    }

    public function testTemplateUnknown()
    {
        $sTemplateJs = trim(jaxon()->template()->render('jaxon::plugins/unknown',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals('', $sTemplateJs);
    }

    public function testView()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('plugins/include.js',
            ['sUrl' => 'http://example.test/path', 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewEmbedded()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('plugins/includes.js',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewJsonConversion()
    {
        $sJs = '{"view":"<script type=\"text\/javascript\" src=\"http:\/\/example.test\/path\"  charset=\"UTF-8\"><\/script>"}';
        $sViewJs = json_encode(['view' => jaxon()->view()->render('plugins/includes.js',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => ''])]);
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewWithNamespace()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('jaxon::plugins/include.js',
            ['sUrl' => 'http://example.test/path', 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewEmbeddedWithNamespace()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('jaxon::plugins/includes.js',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewVarsWith()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('jaxon::plugins/includes.js')
            ->with('aUrls', ['http://example.test/path'])->with('sJsOptions', ''));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewVarsSet()
    {
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->set('aUrls', ['http://example.test/path'])->set('sJsOptions', '')
            ->render('jaxon::plugins/includes.js'));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewVarsShare()
    {
        jaxon()->view()->share('aUrls', ['http://example.test/path']);
        jaxon()->view()->share('sJsOptions', '');
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('jaxon::plugins/includes.js'));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewVarsShareValues()
    {
        jaxon()->view()->shareValues(['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']);
        $sJs = '<script type="text/javascript" src="http://example.test/path"  charset="UTF-8"></script>';
        $sViewJs = trim(jaxon()->view()->render('jaxon::plugins/includes.js'));
        $this->assertEquals($sJs, $sViewJs);
    }

    public function testViewUnknown()
    {
        $sTemplateJs = trim(jaxon()->view()->render('jaxon::plugins/unknown',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals('', $sTemplateJs);
    }

    public function testViewUnknownRenderer()
    {
        jaxon()->di()->getViewRenderer()->addNamespace('nr',
            __DIR__ . '/../../templates/', '.php', 'unknown');
        $sTemplateJs = trim(jaxon()->view()->render('unknown::plugins/includes',
            ['aUrls' => ['http://example.test/path'], 'sJsOptions' => '']));
        $this->assertEquals('', $sTemplateJs);
    }
}
