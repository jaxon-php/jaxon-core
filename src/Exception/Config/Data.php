<?php

namespace Xajax\Config\Exception;

class Data extends \Exception
{
    public function __contruct($sMessage, $sKey, $nDepth = 0)
    {
        parent::__construct(xajax_trans('config.errors.data.' . $sMessage,
        	array('key' => $sKey, 'depth' => $nDepth)));
    }
}
