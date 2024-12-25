<?php

namespace Jaxon\Tests\TestUi;

require_once __DIR__ . '/../src/dialog.php';

use Jaxon\Jaxon;
use Jaxon\Plugin\Response\Dialog\Library\AlertLibrary;
use Jaxon\Dialogs\Bootbox\BootboxLibrary;
use Jaxon\Dialogs\Bootstrap\BootstrapLibrary;
use Jaxon\Dialogs\CuteAlert\CuteAlertLibrary;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Http\UriException;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use Dialog;
use TestDialogLibrary;

use function get_class;
use function Jaxon\jaxon;
use function Jaxon\rq;
use function Jaxon\pm;
use function Jaxon\Dialogs\registerDialogLibraries;

class DialogTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        registerDialogLibraries();
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->setOption('core.request.uri', 'http://example.test/path');
        jaxon()->register(Jaxon::CALLABLE_CLASS, Dialog::class);
        jaxon()->dialog()->registerLibrary(TestDialogLibrary::class, TestDialogLibrary::NAME);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testDialogSettings()
    {
        $xDialogManager = jaxon()->di()->getDialogManager();
        $this->assertEquals('', $xDialogManager->getQuestionLibrary()->getName());
        $this->assertEquals(AlertLibrary::class, get_class($xDialogManager->getQuestionLibrary()));
        $this->assertEquals(AlertLibrary::class, get_class($xDialogManager->getMessageLibrary()));
        $this->assertEquals(null, $xDialogManager->getModalLibrary());

        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        $this->assertEquals(BootstrapLibrary::class, get_class($xDialogManager->getQuestionLibrary()));
        $this->assertEquals(BootstrapLibrary::class, get_class($xDialogManager->getMessageLibrary()));
        $this->assertEquals(BootstrapLibrary::class, get_class($xDialogManager->getModalLibrary()));

        jaxon()->setOption('dialogs.default.modal', 'bootbox');
        jaxon()->setOption('dialogs.default.message', 'bootbox');
        jaxon()->setOption('dialogs.default.question', 'bootbox');
        $this->assertEquals(BootboxLibrary::class, get_class($xDialogManager->getQuestionLibrary()));
        $this->assertEquals(BootboxLibrary::class, get_class($xDialogManager->getMessageLibrary()));
        $this->assertEquals(BootboxLibrary::class, get_class($xDialogManager->getModalLibrary()));
    }

    public function testDialogOptions()
    {
        $xDialogManager = jaxon()->di()->getDialogManager();
        jaxon()->setOption('dialogs.default.message', 'cute');
        $xMessageLibrary = $xDialogManager->getMessageLibrary();
        $this->assertEquals(CuteAlertLibrary::class, get_class($xMessageLibrary));
    }

    public function testDialogDefaultMethods()
    {
        $xDialogManager = jaxon()->di()->getDialogManager();
        jaxon()->setOption('dialogs.default.question', TestDialogLibrary::NAME);
        $xQuestionLibrary = $xDialogManager->getQuestionLibrary();
        $this->assertEquals('https://cdn.jaxon-php.org/libs', $xQuestionLibrary->getUri());
        $this->assertEquals('', $xQuestionLibrary->getSubdir());
        $this->assertEquals('', $xQuestionLibrary->getVersion());
        $this->assertEquals('', $xQuestionLibrary->getJs());
        $this->assertEquals('', $xQuestionLibrary->getScript());
        $this->assertEquals('', $xQuestionLibrary->getReadyScript());

        $xDialogPlugin = jaxon()->di()->getDialogPlugin();
        $this->assertEquals('', $xDialogPlugin->getUri());
        $this->assertEquals('', $xDialogPlugin->getSubdir());
        $this->assertEquals('', $xDialogPlugin->getVersion());
    }

    public function testDialogJsCode()
    {
        jaxon()->setOption('dialogs.lib.use', ['bootbox', 'bootstrap', 'cute']);
        $sJsCode = jaxon()->js();
        $this->assertStringContainsString('bootbox.min.js', $sJsCode);
        $this->assertStringContainsString('bootstrap-dialog.min.js', $sJsCode);
        $this->assertStringContainsString('cute-alert.js', $sJsCode);
    }

    public function testDialogCssCode()
    {
        jaxon()->setOption('dialogs.lib.use', ['bootstrap', 'cute']);
        $sCssCode = jaxon()->css();
        $this->assertStringContainsString('bootstrap-dialog.min.css', $sCssCode);
        $this->assertStringContainsString('cute-alert/style.css', $sCssCode);
    }

    /**
     * @throws UriException
     */
    public function testDialogScriptCode()
    {
        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        jaxon()->setOption('dialogs.lib.use', ['bootbox', 'cute', 'jalert']);

        $sScriptCode = jaxon()->getScript();
        $this->assertStringContainsString("jaxon.dialog.lib.register", $sScriptCode);
        $this->assertStringContainsString("jaxon.dialog.lib.register('bootstrap'", $sScriptCode);
        $this->assertStringContainsString("jaxon.dialog.lib.register('bootbox'", $sScriptCode);
        $this->assertStringContainsString("jaxon.dialog.lib.register('cute'", $sScriptCode);
        $this->assertStringContainsString("jaxon.dialog.lib.register('jalert'", $sScriptCode);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
        $this->assertEquals('dialog', $aCommands[0]['options']['plugin']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
        $this->assertEquals('dialog', $aCommands[0]['options']['plugin']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
        $this->assertEquals('dialog', $aCommands[0]['options']['plugin']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.message', $aCommands[0]['name']);
        $this->assertEquals('dialog', $aCommands[0]['options']['plugin']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.modal.show', $aCommands[0]['name']);
        $this->assertEquals('dialog', $aCommands[0]['options']['plugin']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.modal.show', $aCommands[0]['name']);
    }

    /**
     * @throws RequestException
     */
    public function testDialogLibraryShowWith()
    {
        // Choose the bootstrap library in the options, and use the bootbox in the class.
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.modal.show', $aCommands[0]['name']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('dialog.modal.hide', $aCommands[0]['name']);
        $this->assertEquals('dialog', $aCommands[0]['options']['plugin']);
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageSuccess()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'cute');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Sample.method",' .
                '"args":[{"_type":"html","_name":"elt_id"}]}],' .
                '"question":{"lib":"noty","title":"","phrase":{"str":"Really?","args":[]}},' .
                '"message":{"lib":"cute","type":"success","title":"","phrase":{"str":"No confirm","args":[]}}})',
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseSuccess("No confirm")->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageInfo()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'cute');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Sample.method",' .
                '"args":[{"_type":"html","_name":"elt_id"}]}],' .
                '"question":{"lib":"noty","title":"","phrase":{"str":"Really?","args":[]}},' .
                '"message":{"lib":"cute","type":"info","title":"","phrase":{"str":"No confirm","args":[]}}})',
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseInfo("No confirm")->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageWarning()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'cute');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Sample.method",' .
                '"args":[{"_type":"html","_name":"elt_id"}]}],' .
                '"question":{"lib":"noty","title":"","phrase":{"str":"Really?","args":[]}},' .
                '"message":{"lib":"cute","type":"warning","title":"","phrase":{"str":"No confirm","args":[]}}})',
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseWarning("No confirm")->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageError()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'cute');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Sample.method",' .
                '"args":[{"_type":"html","_name":"elt_id"}]}],' .
                '"question":{"lib":"noty","title":"","phrase":{"str":"Really?","args":[]}},' .
                '"message":{"lib":"cute","type":"error","title":"","phrase":{"str":"No confirm","args":[]}}})',
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseError("No confirm")->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testErrorRegisterIncorrectDialogClass()
    {
        $this->expectException(SetupException::class);
        jaxon()->dialog()->registerLibrary(Dialog::class, 'incorrect');
    }

    public function testErrorSetWrongMessageLibrary()
    {
        $this->expectException(SetupException::class);
        jaxon()->setOption('dialogs.default.message', 'incorrect');
    }

    public function testErrorSetWrongModalLibrary()
    {
        $this->expectException(SetupException::class);
        jaxon()->setOption('dialogs.default.modal', 'incorrect');
    }

    public function testErrorSetWrongQuestionLibrary()
    {
        $this->expectException(SetupException::class);
        jaxon()->setOption('dialogs.default.question', 'incorrect');
    }
}
