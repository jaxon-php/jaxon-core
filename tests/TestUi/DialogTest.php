<?php

namespace Jaxon\Tests\TestUi;

require_once __DIR__ . '/../src/dialog.php';

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Dialogs\Library\Bootbox\BootboxLibrary;
use Jaxon\Dialogs\Library\Bootstrap\BootstrapLibrary;
use Jaxon\Dialogs\Library\Toastr\ToastrLibrary;
use Jaxon\Utils\Http\UriException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use TestDialogLibrary;

use function jaxon;

class DialogTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->setOption('core.request.uri', 'http://example.test/path');
        jaxon()->setOption('dialogs.assets.include.all', true);
        jaxon()->setOption('dialogs.toastr.options.closeButton', true);
        jaxon()->setOption('dialogs.toastr.options.positionClass', 'toast-top-center');
        jaxon()->setOption('dialogs.toastr.options.sampleArray', ['value']);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Dialog');
        jaxon()->registerDialogLibrary(BootboxLibrary::class, BootboxLibrary::NAME);
        jaxon()->registerDialogLibrary(BootstrapLibrary::class, BootstrapLibrary::NAME);
        jaxon()->registerDialogLibrary(ToastrLibrary::class, ToastrLibrary::NAME);
        jaxon()->registerDialogLibrary(TestDialogLibrary::class, TestDialogLibrary::NAME);

        // Register the template dir into the template renderer
        jaxon()->template()->addNamespace('jaxon::dialogs',
            dirname(__DIR__, 2) . '/vendor/jaxon-php/jaxon-dialogs/templates');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testDialogJsCode()
    {
        $sJsCode = jaxon()->getJs();
        $this->assertStringContainsString('bootbox.min.js', $sJsCode);
        $this->assertStringContainsString('bootstrap-dialog.min.js', $sJsCode);
        $this->assertStringContainsString('toastr.min.js', $sJsCode);
    }

    public function testDialogCssCode()
    {
        $sCssCode = jaxon()->getCss();
        $this->assertStringContainsString('bootstrap-dialog.min.css', $sCssCode);
        $this->assertStringContainsString('toastr.min.css', $sCssCode);
    }

    /**
     * @throws UriException
     */
    public function testDialogScriptCode()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        $sScriptCode = jaxon()->getScript();
        $this->assertStringContainsString('jaxon.dialogs = {}', $sScriptCode);
        $this->assertStringContainsString('jaxon.dialogs.bootstrap', $sScriptCode);
        $this->assertStringContainsString('jaxon.dialogs.bootbox', $sScriptCode);
        $this->assertStringContainsString('jaxon.dialogs.toastr', $sScriptCode);
        $this->assertStringContainsString('jaxon.command.handler.register', $sScriptCode);
        $this->assertStringContainsString('jaxon.ajax.message', $sScriptCode);
        $this->assertStringContainsString('jaxon.dialogs.toastr', $sScriptCode);
    }

    /**
     * @throws RequestException
     */
    public function testDefaultDialogSuccess()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'success',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDefaultDialogWarning()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'warning',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDefaultDialogInfo()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'info',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDefaultDialogError()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'error',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibrarySuccess()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'success',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryWarning()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'warning',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryInfo()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'info',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryError()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'error',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryShow()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'show',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testBootboxLibraryShow()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootbox');
        jaxon()->setOption('dialogs.default.message', 'bootbox');
        jaxon()->setOption('dialogs.default.question', 'bootbox');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'show',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(3, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryShowWith()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'showWith',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(3, $xResponse->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryHide()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Dialog',
                'jxnmthd' => 'hide',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertCount(1, $xResponse->getCommands());
    }
}
