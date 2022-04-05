<?php

namespace Jaxon\Tests\TestRegistration;

use Jaxon\Exception\SetupException;
use Jaxon\Dialogs\DialogPlugin;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->registerPlugin(DialogPlugin::class, DialogPlugin::NAME);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testPlugin()
    {
        $this->assertNotNull(jaxon()->getResponse()->dialog);
        $this->assertNotNull(jaxon()->plugin('dialog'));
        $this->assertEquals(DialogPlugin::class, get_class(jaxon()->getResponse()->dialog));
        $this->assertEquals(DialogPlugin::class, get_class(jaxon()->plugin('dialog')));
    }

    public function testRegisterInvalidPlugin()
    {
        require_once __DIR__ . '/../src/sample.php';
        // Register a class which is not a plugin as a plugin.
        $this->expectException(SetupException::class);
        jaxon()->registerPlugin('Sample', 'sample');
    }

    public function testGetUnknownPlugin()
    {
        $this->assertNull(jaxon()->getResponse()->unknown);
        $this->assertNull(jaxon()->plugin('unknown'));
    }
}
