<?php

namespace Jaxon\Tests\Ns\Annotated;

use Jaxon\App\CallableClass;

/**
 * @exclude(false)
 * @databag('name' => 'user.name')
 * @databag('name' => 'page.number')
 * @before('call' => 'funcBefore1')
 * @before('call' => 'funcBefore2')
 * @after('call' => 'funcAfter1')
 * @after('call' => 'funcAfter2')
 * @after('call' => 'funcAfter3')
 */
class ClassAnnotated extends CallableClass
{
}
