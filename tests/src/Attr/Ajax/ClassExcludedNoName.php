<?php
declare(strict_types=1);

namespace Jaxon\Tests\App\Attr\Ajax;

use Jaxon\App\Attribute\After;
use Jaxon\App\Attribute\Before;
use Jaxon\App\Attribute\DataBag;
use Jaxon\App\Attribute\Exclude;
use Jaxon\Tests\App\Attr\CallableClass;

#[Exclude(true)]
class ClassExcludedNoName extends CallableClass
{
    #[Exclude]
    public function doNot()
    {
    }

    #[DataBag('user.name')]
    #[DataBag('page.number')]
    public function withBags()
    {
    }

    #[Before('funcBefore')]
    #[After('funcAfter')]
    public function cbSingle()
    {
    }
}
