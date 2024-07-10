<?php

/**
 * AnnotationReader.php
 *
 * Jaxon annotation reader.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Attribute;

use Jaxon\App\Attribute\AbstractAttribute;
use Jaxon\App\Attribute\DI as DiAttribute;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AnnotationReaderInterface;
use Error;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

use function array_filter;
use function array_merge;
use function count;
use function is_a;

class AttributeReader implements AnnotationReaderInterface
{
    /**
     * @var ReflectionClass
     */
    protected $xReflectionClass;

    /**
     * Imports defined with "use" statements in file headers.
     *
     * @var array
     */
    protected $aImportedTypes;

    /**
     * Properties types.
     *
     * @var array
     */
    protected $aPropertyTypes;

    /**
     * @param AttributeParser $xParser
     * @param string $sCacheDir
     */
    public function __construct(private AttributeParser $xParser, private string $sCacheDir)
    {}

    /**
     * Slugify a string
     *
     * @param string $str
     *
     * @return string
     */
    private function slugify(string $str): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str), '-'));
    }

    private function log(string $msg)
    {
        // Save the types in the cache file
        $sFileName = $this->xReflectionClass->getFileName();
        $sFilePath = $this->sCacheDir . '/' . $this->slugify($sFileName) . '.log';
        file_put_contents($sFilePath, $msg . "\n", FILE_APPEND);
    }

    /**
     * Read the property types
     *
     * @return void
     */
    private function readImportedTypes()
    {
        $sClass = $this->xReflectionClass->getName();
        if(isset($this->aImportedTypes[$sClass]))
        {
            return;
        }

        $this->aImportedTypes[$sClass] = $this->xParser->readImportedTypes($this->xReflectionClass);
    }

    /**
     * Read the property types
     *
     * @return void
     */
    private function readPropertyTypes()
    {
        $sClass = $this->xReflectionClass->getName();
        if(isset($this->aPropertyTypes[$sClass]))
        {
            return;
        }

        $this->aPropertyTypes[$sClass] = [];
        $nFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
        $aProperties = $this->xReflectionClass->getProperties($nFilter);
        foreach($aProperties as $xReflectionProperty)
        {
            $xType = $xReflectionProperty->getType();
            // Check that the property has a valid type defined
            if(is_a($xType, ReflectionNamedType::class) && $xType->getName() !== '')
            {
                $this->aPropertyTypes[$sClass][$xReflectionProperty->getName()] = $xType->getName();
            }
        }
    }

    /**
     * @param ReflectionAttribute $xAttribute
     * @param array $aValues The current values of the attribute
     * @param string $sProperty
     *
     * @return array
     * @throws SetupException
     */
    private function getAttrValue(ReflectionAttribute $xAttribute, array $aValues, string $sProperty = ''): array
    {
        $xInstance = $xAttribute->newInstance();
        $xInstance->setTarget($xAttribute->getTarget());
        $xInstance->setNamespace($this->xReflectionClass->getNamespaceName());
        $sName = $xInstance->getName();
        if(is_a($xInstance, DiAttribute::class))
        {
            $sClass = $this->xReflectionClass->getName();
            $xInstance->setTypes($this->aImportedTypes[$sClass], $this->aPropertyTypes[$sClass]);
            if($sProperty !== '')
            {
                $xInstance->setProperty($sProperty);
            }
            $this->log('Set types on DI: ' . json_encode([
                'name' => $sName,
                'class' => $sClass,
                'target' => $xAttribute->getTarget(),
                'property' => $sProperty,
                'imported' => $this->aImportedTypes[$sClass],
                'property' => $this->aPropertyTypes[$sClass],
            ]));
        }

        $this->log('Attribute arguments: ' . json_encode([
            'name' => $sName,
            'arguments' => $xAttribute->getArguments(),
        ]));
        $xInstance->validateArguments($xAttribute->getArguments());
        $xInstance->setPrevValue($aValues[$sName] ?? null);

        return [$sName, $xInstance->getValidatedValue()];
    }

    /**
     * @param array<ReflectionAttribute> $aAttributes
     *
     * @return array
     * @throws SetupException
     */
    private function getAttrValues(array $aAttributes): array
    {
        // Only keep our attributes.
        $aAttributes = array_filter($aAttributes, function($xAttribute) {
            return is_a($xAttribute->getName(), AbstractAttribute::class, true);
        });

        $aValues = [];
        foreach($aAttributes as $xAttribute)
        {
            [$sName, $xValue] = $this->getAttrValue($xAttribute, $aValues);
            if($sName !== 'protected' || ($xValue)) // Ignore attribute Exclude with value true
            {
                $aValues[$sName] = $xValue;
            }
            $this->log('Attribute value: ' . json_encode(['attr' => $xAttribute->getName(),
                'name' => $sName, 'value' => $xValue, 'values' => $aValues]));
        }
        return $aValues;
    }

    /**
     * @param string $sProperty
     * @param array<ReflectionAttribute> $aAttributes
     *
     * @return array
     * @throws SetupException
     */
    private function getPropertyAttrValues(string $sProperty): array
    {
        // Only keep our attributes.
        $aAttributes = $this->xReflectionClass->getProperty($sProperty)->getAttributes();
        $aAttributes = array_filter($aAttributes, function($xAttribute) {
            // Only DI attributes are allowed on properties
            return is_a($xAttribute->getName(), DiAttribute::class, true);
        });

        $nCount = count($aAttributes);
        if($nCount === 0)
        {
            return ['', null];
        }
        if($nCount > 1)
        {
            throw new SetupException('Only one DI attribute is allowed on a property');
        }

        return $this->getAttrValue($aAttributes[0], [], $sProperty);
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function getAttributes(ReflectionClass|string $xReflectionClass,
        array $aMethods = [], array $aProperties = []): array
    {
        $this->xReflectionClass = is_string($xReflectionClass) ?
            new ReflectionClass($xReflectionClass) : $xReflectionClass;
        $this->readImportedTypes();
        $this->readPropertyTypes();

        try
        {
            // Processing properties attributes
            $aPropAttrs = [];
            // Properties attributes
            foreach($aProperties as $sProperty)
            {
                [$sName, $xValue] = $this->getPropertyAttrValues($sProperty);
                if($xValue !== null)
                {
                    $aPropAttrs[$sName] = array_merge($aPropAttrs[$sName] ?? [], $xValue);
                }
            }

            // Processing class attributes
            $aClassAttrs = $this->getAttrValues($this->xReflectionClass->getAttributes());
            if(isset($aClassAttrs['protected']))
            {
                return [true, [], []]; // The entire class is not to be exported.
            }

            // Merge attributes and class attributes
            foreach($aPropAttrs as $sName => $xValue)
            {
                $aClassAttrs[$sName] = array_merge($aClassAttrs[$sName] ?? [], $xValue);
            }

            // Processing methods attributes
            $aAttrValues = count($aClassAttrs) > 0 ? ['*' => $aClassAttrs] : [];
            $aProtected = [];
            foreach($aMethods as $sMethod)
            {
                $aAttributes = $this->xReflectionClass->getMethod($sMethod)->getAttributes();
                $aMethodAttrs = $this->getAttrValues($aAttributes);

                if(isset($aMethodAttrs['protected']))
                {
                    $aProtected[] = $sMethod; // The method is not to be exported.
                }
                elseif(count($aMethodAttrs) > 0)
                {
                    $aAttrValues[$sMethod] = $aMethodAttrs;
                }
            }

            return [false, $aAttrValues, $aProtected];
        }
        catch(Exception|Error $e)
        {
            throw new SetupException($e->getMessage());
        }
    }
}
