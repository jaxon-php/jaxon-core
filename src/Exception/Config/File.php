<?php

namespace Xajax\Config\Exception;

class File extends \Exception
{
    public function __contruct($sMessage, $sPath)
    {
        parent::__construct(xajax_trans('config.errors.file.' . $sMessage, array('path' => $sPath)));
    }
}
