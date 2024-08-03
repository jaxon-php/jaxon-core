<?php
declare(strict_types=1);

namespace Jaxon\Tests\App\Attr\Ajax;

use Jaxon\App\Attribute\After;
use Jaxon\App\Attribute\Before;
use Jaxon\App\Attribute\DataBag;
use Jaxon\App\Attribute\DI;
use Jaxon\App\Attribute\Exclude;
use Jaxon\App\Attribute\Upload;
use Jaxon\Tests\App\Attr\CallableClass;
use Jaxon\Tests\Service\ColorService;

class AttributeNoName extends CallableClass
{
    #[Exclude(true)]
    public function doNot()
    {
    }

    #[Exclude('Me')]
    public function doNotError()
    {
    }

    #[Databag('user.name')]
    #[Databag('page.number')]
    public function withBags()
    {
    }

    #[Databag('user:name')]
    public function withBagsErrorName()
    {
    }

    #[Databag('page number')]
    public function withBagsErrorNumber()
    {
    }

    #[Upload('user-files')]
    #[Exclude(false)]
    public function saveFiles()
    {
    }

    #[Upload('user:file')]
    public function saveFileErrorFieldName()
    {
    }

    #[Upload('user file')]
    public function saveFileErrorFieldNumber()
    {
    }

    #[Upload('user-files')]
    public function saveFilesWrongName()
    {
    }

    #[Upload('user-file1')]
    #[Upload('user-file2')]
    public function saveFilesMultiple()
    {
    }

    #[Before('funcBefore')]
    #[After('funcAfter')]
    public function cbSingle()
    {
    }

    #[Before('funcBefore1')]
    #[Before('funcBefore2')]
    #[After('funcAfter1')]
    #[After('funcAfter2')]
    #[After('funcAfter3')]
    public function cbMultiple()
    {
    }

    #[Before('funcBefore1', ["param1"])]
    #[Before('funcBefore2', ["param1", "param2"])]
    #[After('funcAfter1', ["param1", "param2"])]
    public function cbParams()
    {
    }

    #[Before('func:Before')]
    public function cbBeforeErrorName()
    {
    }

    #[Before('funcBefore', false)]
    public function cbBeforeErrorParam()
    {
    }

    #[Before('funcBefore', ["param1"], false)]
    public function cbBeforeErrorNumber()
    {
    }

    #[Before('func:After')]
    public function cbAfterErrorName()
    {
    }

    #[Before('funcAfter', false)]
    public function cbAfterErrorParam()
    {
    }

    #[Before('funcAfter', ["param1"], false)]
    public function cbAfterErrorNumber()
    {
    }

    #[DI('ColorService', 'colorService')]
    #[DI('FontService', 'fontService')]
    public function di1()
    {
    }

    #[DI('ColorService', 'colorService')]
    #[DI('\Jaxon\Tests\Service\TextService', 'textService')]
    public function di2()
    {
    }

    #[DI('ColorService', 'color.Service')]
    public function diErrorAttr()
    {
    }

    #[DI('Color.Service', 'colorService')]
    public function diErrorClass()
    {
    }

    #[DI('colorService')]
    public function diErrorOneParam()
    {
    }

    #[DI('ColorService', 'TextService', 'colorService')]
    public function diErrorThreeParams()
    {
    }
}
