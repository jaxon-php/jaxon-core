<?php

namespace Jaxon\Tests\Ns\Annotated;

use Jaxon\App\CallableClass;

/**
 * @exclude(true)
 */
class ClassExcluded extends CallableClass
{
    /**
     * @exclude
     */
    public function doNot()
    {
    }

    /**
     * @databag('name' => 'user.name')
     * @databag('name' => 'page.number')
     */
    public function withBags()
    {
    }

    /**
     * @before('call' => 'funcBefore')
     * @after('call' => 'funcAfter')
     */
    public function cbSingle()
    {
    }
}
