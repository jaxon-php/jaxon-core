<?php
declare(strict_types=1);

namespace Jaxon\Tests\App\Attr\Ajax;

use Jaxon\App\Attribute\DI;
use Jaxon\Tests\App\Attr\CallableClass;
use Jaxon\Tests\Service\SubDir;

class SubDirImportAttribute extends CallableClass
{
    protected SubDir\FirstService $firstService;

    #[DI('SubDir\SecondService')]
    protected $secondService;

    #[DI('firstService')]
    public function attrDi()
    {
    }
}
