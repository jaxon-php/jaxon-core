<?php

/**
 * DI.php
 *
 * Jaxon attribute.
 * Specifies attributes to inject into a callable object.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Attribute;

use Attribute;

use function is_array;
use function ltrim;
use function preg_match;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DI extends AbstractAttribute
{
    /**
     * The injected attribute name
     *
     * @var string
     */
    protected $sAttr = '';

    /**
     * The injected attribute type
     *
     * @var string
     */
    protected $sType = '';

    /**
     * Imported types for the current class.
     *
     * @var array
     */
    protected $aImportedTypes;

    /**
     * Properties types for the current class.
     *
     * @var array
     */
    protected $aPropertyTypes;

    /**
     * @param string $type
     * @param string $attr
     */
    public function __construct(string $type = '', string $attr = '')
    {
        $this->sType = $type;
        $this->sAttr = $attr;
    }

    /**
     * @param string $sAttr
     *
     * @return void
     */
    public function setAttr(string $sAttr)
    {
        $this->sAttr = $sAttr;
    }

    /**
     * @param array $aImportedTypes
     * @param array $aPropertyTypes
     *
     * @return void
     */
    public function setTypes(array $aImportedTypes, array $aPropertyTypes)
    {
        $this->aImportedTypes = $aImportedTypes;
        $this->aPropertyTypes = $aPropertyTypes;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '__di';
    }

    /**
     * @return string
     */
    private function getFullClassName(): string
    {
        if($this->sType === '')
        {
            // If no type is provided, take the attribute type.
            return $this->aPropertyTypes[$this->sAttr] ?? '';
        }
        if($this->sType[0] === '\\')
        {
            return ltrim($this->sType, '\\');
        }

        // Try to resolve the full class name
        $nSeparatorPosition = strpos($this->sType, '\\');
        $sClassprefix = $nSeparatorPosition === false ? $this->sType : substr($this->sType, 0, $nSeparatorPosition);
        if(isset($this->aImportedTypes[$sClassprefix]))
        {
            // The class namespace is imported.
            return $nSeparatorPosition === false ? $this->aImportedTypes[$sClassprefix] :
                $this->aImportedTypes[$sClassprefix] . substr($this->sType, $nSeparatorPosition);
        }

        // The class is in the current namespace.
        return $this->sNamespace . '\\'. $this->sType;
    }

    /**
     * @return bool
     */
    protected function validateType(): bool
    {
        return preg_match('/^(\\\)?([a-zA-Z][a-zA-Z0-9_]*)(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $this->sType) > 0;
    }

    /**
     * @return bool
     */
    protected function validateAttr(): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->sAttr) > 0;
    }

    /**
     * @inheritDoc
     */
    protected function validate(): bool
    {
        // For a property, the only parameter is the type. Otherwise, it is the attribute.
        if($this->xTarget === Attribute::TARGET_PROPERTY)
        {
            if($this->sAttr !== '')
            {
                $this->setError('The only property of the DI attribute must be the type');
                return false;
            }
            if(!$this->validateType())
            {
                $this->setError($this->sType . ' is not a valid "type" value for the DI attribute');
                return false;
            }

            return true;
        }

        if(!$this->validateType())
        {
            $this->setError($this->sType . ' is not a valid "type" value for the DI attribute');
            return false;
        }
        if(!$this->validateAttr())
        {
            $this->setError($this->sAttr . ' is not a valid "attr" value for the DI attribute');
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function getValue()
    {
        if($this->xTarget === Attribute::TARGET_METHOD)
        {
            // For a method, if only one parameter is provided, then make it the attribute name.
            if($this->sAttr === '' && $this->sType !== '')
            {
                $this->sAttr = $this->sType;
                $this->sType = '';
            }
        }

        if(is_array($this->xPrevValue))
        {
            // Append the current value to the array
            $this->xPrevValue[$this->sAttr] = $this->getFullClassName();
            return $this->xPrevValue;
        }
        // Return the current value in an array
        return [$this->sAttr => $this->getFullClassName()];
    }
}
