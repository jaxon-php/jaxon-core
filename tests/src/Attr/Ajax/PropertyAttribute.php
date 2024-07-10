<?php
declare(strict_types=1);

namespace Jaxon\Tests\App\Attr\Ajax;

use Jaxon\App\Attribute\DI;
use Jaxon\Tests\App\Attr\CallableClass;
use Jaxon\Tests\Service\ColorService;

class PropertyAttribute extends CallableClass
{
    protected ColorService $colorService;

    protected FontService $fontService;

    protected \Jaxon\Tests\Service\TextService $textService;

    #[DI('ColorService')]
    protected $colorService1;

    #[DI('FontService')]
    protected $fontService1;

    #[DI('\Jaxon\Tests\Service\TextService')]
    protected $textService1;

    #[DI]
    protected ColorService $colorService2;

    #[DI]
    protected FontService $fontService2;

    #[DI]
    protected \Jaxon\Tests\Service\TextService $textService2;

    #[DI(type: 'ColorService')]
    protected $colorService3;

    #[DI(type: 'FontService')]
    protected $fontService3;

    #[DI(type: '\Jaxon\Tests\Service\TextService')]
    protected $textService3;

    #[DI('FontService', 'fontService')]
    protected $errorTwoParams;

    #[DI(attr: 'fontService')]
    protected $errorDiAttr;

    #[DI]
    #[DI('FontService')]
    protected $errorTwoDi;

    #[DI(attr: 'colorService')]
    #[DI(attr: 'fontService')]
    #[DI(attr: 'textService')]
    public function attrVar()
    {
    }

    #[DI('colorService')]
    #[DI('fontService')]
    #[DI('textService')]
    public function attrDbVar()
    {
    }

    public function attrDi()
    {
    }

    #[DI(type: 'ColorService')]
    public function errorDiClass()
    {
    }
}
