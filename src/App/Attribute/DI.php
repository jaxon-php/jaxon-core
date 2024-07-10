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
use Jaxon\Exception\SetupException;

use function count;
use function is_array;
use function ltrim;
use function preg_match;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DI extends AbstractAttribute
{
    /**
     * The injected property type
     *
     * @var string
     */
    protected $sPropertyType = '';

    /**
     * The injected property name
     *
     * @var string
     */
    protected $sPropertyName = '';

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
        $this->sPropertyType = $type;
        $this->sPropertyName = $attr;
    }

    /**
     * @param string $sPropertyName
     *
     * @return void
     */
    public function setProperty(string $sPropertyName)
    {
        $this->sPropertyName = $sPropertyName;
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
     * @inheritDoc
     */
    public function validateArguments(array $aArguments)
    {
        $nArgCount = count($aArguments);
        if($nArgCount > 2)
        {
            throw new SetupException('The DI attribute requires cannot take more than two arguments.');
        }
        if($this->xTarget === Attribute::TARGET_CLASS)
        {
            if($nArgCount !== 2)
            {
                throw new SetupException('When applied to a class, the DI attribute requires two arguments.');
            }
            return;
        }
        if($this->xTarget === Attribute::TARGET_METHOD)
        {
            if($nArgCount !== 1 && $nArgCount !== 2)
            {
                throw new SetupException('When applied to a method, the DI attribute requires one or two arguments.');
            }
            return;
        }
        if($this->xTarget === Attribute::TARGET_PROPERTY)
        {
            if($nArgCount !== 0 && $nArgCount !== 1)
            {
                throw new SetupException('When applied to a property, the DI attribute requires one or no argument.');
            }
        }
    }

    /**
     * @return bool
     */
    protected function validateType(): bool
    {
        return preg_match('/^(\\\)?([a-zA-Z][a-zA-Z0-9_]*)(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $this->sPropertyType) > 0;
    }

    /**
     * @return bool
     */
    protected function validateName(): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->sPropertyName) > 0;
    }

    /**
     * @inheritDoc
     */
    protected function validateValues()
    {
        if($this->xTarget === Attribute::TARGET_PROPERTY)
        {
            // if($this->sPropertyName !== '')
            // {
            //     throw new SetupException('The "property" argument must not be set on the DI attribute on a property.');
            // }
        }
        elseif($this->xTarget === Attribute::TARGET_METHOD)
        {
            // For a method, if only one parameter is provided, then make it the attribute name.
            if($this->sPropertyName === '' && $this->sPropertyType !== '')
            {
                $this->sPropertyName = $this->sPropertyType;
                $this->sPropertyType = '';
            }
            if($this->sPropertyName === '')
            {
                throw new SetupException('The "property" argument is mandatory on the DI attribute on a method.');
            }
        }
        else // if($this->xTarget === Attribute::TARGET_CLASS)
        {
            if($this->sPropertyType === '')
            {
                throw new SetupException('The "type" argument is mandatory on the DI attribute on a class.');
            }
            if($this->sPropertyName === '')
            {
                throw new SetupException('The "property" argument is mandatory on the DI attribute on a class.');
            }
        }

        if(!$this->validateName())
        {
            throw new SetupException($this->sPropertyName . ' is not a valid "property" value for the DI attribute.');
        }
        // An empty type is allowed on properties and methods...
        if(($this->sPropertyType !== '' || $this->xTarget === Attribute::TARGET_CLASS) && !$this->validateType())
        {
            throw new SetupException($this->sPropertyType . ' is not a valid "type" value for the DI attribute.');
        }
        // But in this case the type must be resolved from the property type.
        $this->sPropertyType = $this->getFullClassName();
        if($this->sPropertyType === '')
        {
            throw new SetupException('An empty "type" value is not allowed for the DI attribute.');
        }
    }

    /**
     * @return string
     */
    private function getFullClassName(): string
    {
        if($this->sPropertyType === '')
        {
            // If no type is provided, take the attribute type.
            return $this->aPropertyTypes[$this->sPropertyName] ?? '';
        }
        if($this->sPropertyType[0] === '\\')
        {
            return ltrim($this->sPropertyType, '\\');
        }

        // Try to resolve the full class name
        $nSeparatorPosition = strpos($this->sPropertyType, '\\');
        $sClassprefix = $nSeparatorPosition === false ? $this->sPropertyType :
            substr($this->sPropertyType, 0, $nSeparatorPosition);
        if(isset($this->aImportedTypes[$sClassprefix]))
        {
            // The class namespace is imported.
            return $nSeparatorPosition === false ? $this->aImportedTypes[$sClassprefix] :
                $this->aImportedTypes[$sClassprefix] . substr($this->sPropertyType, $nSeparatorPosition);
        }

        // The class is in the current namespace.
        return $this->sNamespace . '\\'. $this->sPropertyType;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    protected function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            // Append the current value to the array
            $this->xPrevValue[$this->sPropertyName] = $this->sPropertyType;
            return $this->xPrevValue;
        }
        // Return the current value in an array
        return [$this->sPropertyName => $this->sPropertyType];
    }
}
