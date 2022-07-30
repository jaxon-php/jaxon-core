<?php

namespace Jaxon\Tests\TestRegistration;

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Response\Dialog\DialogPlugin;
use PHPUnit\Framework\TestCase;
use function Jaxon\jaxon;

class PluginTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
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
        $this->assertEquals(DialogPlugin::NAME, jaxon()->getResponse()->dialog->getname());
        $this->assertEquals(DialogPlugin::NAME, jaxon()->plugin('dialog')->getname());
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
