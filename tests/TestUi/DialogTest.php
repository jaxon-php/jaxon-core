<?php

namespace Jaxon\Tests\TestUi;

require_once __DIR__ . '/../../vendor/jaxon-php/jaxon-dialogs/src/start.php';
require_once __DIR__ . '/../src/dialog.php';

use Jaxon\Jaxon;
use Jaxon\App\Dialog\Library\AlertLibrary;
use Jaxon\Dialogs\Bootbox\BootboxLibrary;
use Jaxon\Dialogs\Bootstrap\BootstrapLibrary;
use Jaxon\Dialogs\Toastr\ToastrLibrary;
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
        jaxon()->setOption('dialogs.assets.include.all', true);
        jaxon()->setOption('dialogs.toastr.options.closeButton', true);
        jaxon()->setOption('dialogs.toastr.options.positionClass', 'toast-top-center');
        jaxon()->setOption('dialogs.toastr.options.sampleArray', ['value']);
        jaxon()->register(Jaxon::CALLABLE_CLASS, Dialog::class);
        jaxon()->dialog()->registerLibrary(TestDialogLibrary::class, TestDialogLibrary::NAME);

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

    public function testDialogSettings()
    {
        $xDialogLibraryManager = jaxon()->di()->getDialogLibraryManager();
        $this->assertEquals('', $xDialogLibraryManager->getQuestionLibrary()->getName());
        $this->assertEquals(AlertLibrary::class, get_class($xDialogLibraryManager->getQuestionLibrary()));
        $this->assertEquals(AlertLibrary::class, get_class($xDialogLibraryManager->getMessageLibrary()));
        $this->assertEquals(null, $xDialogLibraryManager->getModalLibrary());

        jaxon()->setOption('dialogs.default.modal', 'bootstrap');
        jaxon()->setOption('dialogs.default.message', 'bootstrap');
        jaxon()->setOption('dialogs.default.question', 'bootstrap');
        $this->assertEquals(BootstrapLibrary::class, get_class($xDialogLibraryManager->getQuestionLibrary()));
        $this->assertEquals(BootstrapLibrary::class, get_class($xDialogLibraryManager->getMessageLibrary()));
        $this->assertEquals(BootstrapLibrary::class, get_class($xDialogLibraryManager->getModalLibrary()));

        jaxon()->setOption('dialogs.default.modal', 'bootbox');
        jaxon()->setOption('dialogs.default.message', 'bootbox');
        jaxon()->setOption('dialogs.default.question', 'bootbox');
        $this->assertEquals(BootboxLibrary::class, get_class($xDialogLibraryManager->getQuestionLibrary()));
        $this->assertEquals(BootboxLibrary::class, get_class($xDialogLibraryManager->getMessageLibrary()));
        $this->assertEquals(BootboxLibrary::class, get_class($xDialogLibraryManager->getModalLibrary()));
    }

    public function testDialogOptions()
    {
        $xDialogLibraryManager = jaxon()->di()->getDialogLibraryManager();
        jaxon()->setOption('dialogs.default.message', 'toastr');
        $xMessageLibrary = $xDialogLibraryManager->getMessageLibrary();
        $this->assertEquals(ToastrLibrary::class, get_class($xMessageLibrary));
        $this->assertTrue($xMessageLibrary->helper()->hasOption('options.closeButton'));
        $this->assertIsArray($xMessageLibrary->helper()->getOption('options.sampleArray'));
        $this->assertIsString($xMessageLibrary->helper()->getOption('options.positionClass'));
    }

    public function testDialogDefaultMethods()
    {
        $xDialogLibraryManager = jaxon()->di()->getDialogLibraryManager();
        jaxon()->setOption('dialogs.default.question', TestDialogLibrary::NAME);
        $xQuestionLibrary = $xDialogLibraryManager->getQuestionLibrary();
        $xQuestionLibrary->setReturnCode(false);
        $this->assertEquals('https://cdn.jaxon-php.org/libs', $xQuestionLibrary->getUri());
        $this->assertEquals('', $xQuestionLibrary->getSubdir());
        $this->assertEquals('', $xQuestionLibrary->getVersion());
        $this->assertEquals('', $xQuestionLibrary->getJs());
        $this->assertEquals('', $xQuestionLibrary->getScript());
        $this->assertEquals('', $xQuestionLibrary->getReadyScript());

        $xDialogPlugin = jaxon()->di()->getDialogPlugin();
        $xDialogPlugin->setReturnCode(false);
        $this->assertEquals('', $xDialogPlugin->getUri());
        $this->assertEquals('', $xDialogPlugin->getSubdir());
        $this->assertEquals('', $xDialogPlugin->getVersion());
    }

    public function testDialogJsCode()
    {
        jaxon()->setOption('dialogs.lib.use', ['bootbox', 'bootstrap', 'toastr']);
        $sJsCode = jaxon()->js();
        $this->assertStringContainsString('bootbox.min.js', $sJsCode);
        $this->assertStringContainsString('bootstrap-dialog.min.js', $sJsCode);
        $this->assertStringContainsString('toastr.min.js', $sJsCode);
    }

    public function testDialogCssCode()
    {
        jaxon()->setOption('dialogs.lib.use', ['bootstrap', 'toastr']);
        $sCssCode = jaxon()->css();
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
        jaxon()->setOption('dialogs.lib.use', ['bootbox', 'toastr']);
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

        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(1, $aCommands);
        $this->assertEquals('al', $aCommands[0]['cmd']);
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
        $this->assertEquals('bootstrap.alert', $aCommands[0]['cmd']);
        $this->assertEquals('bootstrap', $aCommands[0]['plg']);
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
        $this->assertEquals('al', $aCommands[0]['cmd']);
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
        $this->assertEquals('bootstrap.alert', $aCommands[0]['cmd']);
        $this->assertEquals('bootstrap', $aCommands[0]['plg']);
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
        $this->assertEquals('al', $aCommands[0]['cmd']);
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
        $this->assertEquals('bootstrap.alert', $aCommands[0]['cmd']);
        $this->assertEquals('bootstrap', $aCommands[0]['plg']);
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
        $this->assertEquals('al', $aCommands[0]['cmd']);
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
        $this->assertEquals('bootstrap.alert', $aCommands[0]['cmd']);
        $this->assertEquals('bootstrap', $aCommands[0]['plg']);
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
        $this->assertEquals('bootstrap.show', $aCommands[0]['cmd']);
        $this->assertEquals('bootstrap', $aCommands[0]['plg']);
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
        $this->assertCount(2, $aCommands);
        // The bootbox plugin issues one assign and one script command.
        $this->assertEquals('as', $aCommands[0]['cmd']);
        $this->assertEquals('js', $aCommands[1]['cmd']);
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
        $this->assertCount(2, $aCommands);
        // The bootbox plugin issues one assign and one script command.
        $this->assertEquals('as', $aCommands[0]['cmd']);
        $this->assertEquals('js', $aCommands[1]['cmd']);
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
        // The bootbox plugin issues a single script command.
        $this->assertEquals('bootstrap.hide', $aCommands[0]['cmd']);
        $this->assertEquals('bootstrap', $aCommands[0]['plg']);
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageSuccess()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'toastr');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            "jaxon.dialogs.noty.confirm('Really?','',() => {Sample.method(jaxon.$('elt_id').innerHTML);}," .
                "() => {toastr.success('No confirm');})",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseSuccess("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageInfo()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'toastr');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            "jaxon.dialogs.noty.confirm('Really?','',() => {Sample.method(jaxon.$('elt_id').innerHTML);}," .
                "() => {toastr.info('No confirm');})",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseInfo("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageWarning()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'toastr');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            "jaxon.dialogs.noty.confirm('Really?','',() => {Sample.method(jaxon.$('elt_id').innerHTML);}," .
                "() => {toastr.warning('No confirm');})",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseWarning("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageError()
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->setOption('dialogs.default.message', 'toastr');
        jaxon()->setOption('dialogs.default.question', 'noty');
        $this->assertEquals(
            "jaxon.dialogs.noty.confirm('Really?','',() => {Sample.method(jaxon.$('elt_id').innerHTML);}," .
                "() => {toastr.error('No confirm');})",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseError("No confirm")->getScript()
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
