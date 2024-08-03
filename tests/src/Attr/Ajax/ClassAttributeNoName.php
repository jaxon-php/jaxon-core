<?php
declare(strict_types=1);

namespace Jaxon\Tests\App\Attr\Ajax;

use Jaxon\App\Attribute\After;
use Jaxon\App\Attribute\Before;
use Jaxon\App\Attribute\DataBag;
use Jaxon\App\Attribute\DI;
use Jaxon\App\Attribute\Exclude;
use Jaxon\Tests\App\Attr\CallableClass;
use Jaxon\Tests\Service\TextService;

#[Exclude(false)]
#[Databag('user.name')]
#[Databag('page.number')]
#[Before('funcBefore1')]
#[Before('funcBefore2')]
#[After('funcAfter1')]
#[After('funcAfter2')]
#[After('funcAfter3')]
#[DI('\Jaxon\Tests\Service\ColorService', 'colorService')]
#[DI('TextService', 'textService')]
#[DI('FontService', 'fontService')]
class ClassAttributeNoName extends CallableClass
{
}
