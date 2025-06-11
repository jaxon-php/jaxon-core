<?php

namespace Jaxon\Tests;

require_once dirname(__DIR__) . '/src/globals.php';

use PHPUnit\Framework;

class JaxonTestListener implements Framework\TestListener
{
    use Framework\TestListenerDefaultImplementation;
}
