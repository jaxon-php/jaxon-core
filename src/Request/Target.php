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

use function trim;

class Target implements TargetInterface
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
     * The constructor
     *
     * @param string $sType    The target type
     * @param string $sFunctionName    The function name
     * @param string $sClassName    The class name
     * @param string $sMethodName    The method name
     * @param array $aMethodArgs    The method args
     */
    private function __construct(private string $sType, private string $sFunctionName,
        private string $sClassName, private string $sMethodName, private array $aMethodArgs = [])
    {}

    /**
     * Create a target of type Function
     *
     * @param string $sFunctionName    The function name
     *
     * @return Target
     */
    public static function makeFunction(string $sFunctionName): Target
    {
        return new Target(self::TYPE_FUNCTION, $sFunctionName, '', '');
    }

    /**
     * Create a target of type Class
     *
     * @param array $aCall
     *
     * @return Target
     */
    public static function makeClass(array $aCall): Target
    {
        return new Target(self::TYPE_CLASS, '', trim($aCall['name']), trim($aCall['method']));
    }

    /**
     * Check if the target type is Function.
     *
     * @return bool
     */
    public function isFunction(): bool
    {
        return $this->sType === self::TYPE_FUNCTION;
    }

    /**
     * Check if the target type is Class.
     *
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->sType === self::TYPE_CLASS;
    }

    /**
     * The target function name.
     *
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->sFunctionName;
    }

    /**
     * The target class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->sClassName;
    }

    /**
     * The target method name.
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->sMethodName;
    }

    /**
     * Set the target method name.
     *
     * @param array $aMethodArgs
     */
    public function setMethodArgs(array $aMethodArgs): void
    {
        $this->aMethodArgs = $aMethodArgs;
    }

    /**
     * The target method name.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->sMethodName;
    }

    /**
     * The target method args.
     *
     * @return array
     */
    public function args(): array
    {
        return $this->aMethodArgs;
    }
}
