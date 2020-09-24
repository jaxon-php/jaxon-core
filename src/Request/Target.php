<?php

/**
 * Target.php - Jaxon Request Target
 *
 * This class contains the name of the function or class and method targetted by a Jaxon request.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Response\Manager as ResponseManager;

use Exception;

class Target
{
    /**
     * The target type for function.
     *
     * @var string
     */
    const TYPE_FUNCTION = 'TargetFunction';

    /**
     * The target type for class.
     *
     * @var string
     */
    const TYPE_CLASS = 'TargetClass';

    /**
     * The target type.
     *
     * @var string
     */
    private $sType = '';

    /**
     * The target function name.
     *
     * @var string
     */
    private $sFunctionName = '';

    /**
     * The target class name.
     *
     * @var string
     */
    private $sClassName = '';

    /**
     * The target method name.
     *
     * @var string
     */
    private $sMethodName = '';

    /**
     * The constructor
     *
     * @param string    $sType              The target type
     * @param string    $sFunctionName      The function name
     * @param string    $sClassName         The class name
     * @param string    $sMethodName        The method name
     */
    private function __construct($sType, $sFunctionName, $sClassName, $sMethodName)
    {
        $this->sType = $sType;
        $this->sFunctionName = $sFunctionName;
        $this->sClassName = $sClassName;
        $this->sMethodName = $sMethodName;
    }

    /**
     * Create a target of type Function
     *
     * @param string    $sFunctionName      The function name
     */
    public static function makeFunction($sFunctionName)
    {
        return new Target(self::TYPE_FUNCTION, $sFunctionName, '', '');
    }

    /**
     * Create a target of type Class
     *
     * @param string    $sClassName         The class name
     * @param string    $sMethodName        The method name
     */
    public static function makeClass($sClassName, $sMethodName)
    {
        return new Target(self::TYPE_CLASS, '', $sClassName, $sMethodName);
    }

    /**
     * Check if the target type is Function.
     *
     * @return bool
     */
    public function isFunction()
    {
        return $this->sType == self::TYPE_FUNCTION;
    }

    /**
     * Check if the target type is Class.
     *
     * @return bool
     */
    public function isClass()
    {
        return $this->sType == self::TYPE_CLASS;
    }

    /**
     * The target function name.
     *
     * @return string
     */
    public function getFunctionName()
    {
        return $this->sFunctionName;
    }

    /**
     * The target class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->sClassName;
    }

    /**
     * The target method name.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->sMethodName;
    }
}
