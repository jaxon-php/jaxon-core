<?php

namespace Xajax\Config\Exception;

class Yaml extends \Exception
{
    public function __contruct($sMessage)
    {
        parent::__construct(xajax_trans('config.errors.yaml.' . $sMessage));
    }
}
