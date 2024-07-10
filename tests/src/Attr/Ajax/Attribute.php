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

class Attribute extends CallableClass
{
    #[Exclude]
    public function doNot()
    {
    }

    #[Exclude(true)]
    public function doNotBool()
    {
    }

    #[Exclude('Me')]
    public function doNotError()
    {
    }

    #[DataBag(name: 'user.name')]
    #[DataBag(name: 'page.number')]
    public function withBags()
    {
    }

    #[DataBag(mane: 'user.name')]
    #[DataBag(mane: 'page.number')]
    public function withBagsError()
    {
    }

    #[Upload(field: 'user-files')]
    public function saveFiles()
    {
    }

    #[Upload(name: 'user-files')]
    public function saveFilesWrongName()
    {
    }

    #[Upload(field: 'user-file1')]
    #[Upload(field: 'user-file2')]
    public function saveFilesMultiple()
    {
    }

    #[Before(call: 'funcBefore')]
    #[After(call: 'funcAfter')]
    public function cbSingle()
    {
    }

    #[Before(call: 'funcBefore1')]
    #[Before(call: 'funcBefore2')]
    #[After(call: 'funcAfter1')]
    #[After(call: 'funcAfter2')]
    #[After(call: 'funcAfter3')]
    public function cbMultiple()
    {
    }

    #[Before(call: 'funcBefore1', with: ['param1'])]
    #[Before(call: 'funcBefore2', with: ['param1', 'param2'])]
    #[After(call: 'funcAfter1', with: ['param1', 'param2'])]
    public function cbParams()
    {
    }

    #[DI(type: 'ColorService', attr: 'colorService')]
    #[DI(type: 'FontService', attr: 'fontService')]
    public function di1()
    {
    }

    #[DI(type: 'ColorService', attr: 'colorService')]
    #[DI(type: '\Jaxon\Tests\Service\TextService', attr: 'textService')]
    public function di2()
    {
    }

    #[Before(name: 'funcBefore', with: ['param1'])]
    public function cbBeforeNoCall()
    {
    }

    #[Before(call: 'funcBefore', params: ['param1'])]
    public function cbBeforeUnknownAttr()
    {
    }

    #[Before(call: 'funcBefore', with: 'param1')]
    public function cbBeforeWrongAttrType()
    {
    }

    #[After(name: 'funcAfter', with: ['param1'])]
    public function cbAfterNoCall()
    {
    }

    #[After(call: 'funcAfter', params: ['param1'])]
    public function cbAfterUnknownAttr()
    {
    }

    #[After(call: 'funcAfter', with: true)]
    public function cbAfterWrongAttrType()
    {
    }

    #[DI(attr: 'attr', params: '')]
    public function diUnknownAttr()
    {
    }

    #[DI(type: 'ClassName', attr: [])]
    public function diWrongAttrType()
    {
    }

    #[DI(type: true, attr: 'attr')]
    public function diWrongClassType()
    {
    }
}
