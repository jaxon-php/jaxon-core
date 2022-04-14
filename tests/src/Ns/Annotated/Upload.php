<?php

namespace Jaxon\Tests\Ns\Annotated;

use Jaxon\App\CallableClass;

class Upload extends CallableClass
{
    /**
     * @exclude
     */
    public function doNot()
    {
    }

    /**
     * @upload('field' => 'user-files')
     */
    public function saveFiles()
    {
    }

    /**
     * @databag('name' => 'user.name')
     * @databag('name' => 'page.number')
     */
    public function withBags()
    {
    }
}
