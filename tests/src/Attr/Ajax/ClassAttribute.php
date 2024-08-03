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
#[Databag(name: 'user.name')]
#[Databag(name: 'page.number')]
#[Before(call: 'funcBefore1')]
#[Before(call: 'funcBefore2')]
#[After(call: 'funcAfter1')]
#[After(call: 'funcAfter2')]
#[After(call: 'funcAfter3')]
#[DI(type: '\Jaxon\Tests\Service\ColorService', attr: 'colorService')]
#[DI(type: 'TextService', attr: 'textService')]
#[DI(type: 'FontService', attr: 'fontService')]
class ClassAttribute extends CallableClass
{
}
