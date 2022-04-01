<?php

namespace Jaxon\Tests\RegistrationApp;

use Jaxon\Exception\SetupException;
use Lagdo\TwitterFeed\Client as TwitterClient;
use Lagdo\TwitterFeed\Package as TwitterPackage;
use PHPUnit\Framework\TestCase;

use function jaxon;

class PackageTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(__DIR__ . '/../config/app/package.php');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testContainer()
    {
        $this->assertTrue(jaxon()->di()->h(TwitterClient::class));
        $this->assertTrue(jaxon()->di()->h(TwitterPackage::class));
    }
}
