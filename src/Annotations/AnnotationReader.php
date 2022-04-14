<?php

/**
 * AnnotationReader.php
 *
 * Jaxon annotation manager.
 *
 * @package jaxon-core
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Annotations;

use Jaxon\Annotations\Annotation\AbstractAnnotation;
use Jaxon\Annotations\Annotation\AfterAnnotation;
use Jaxon\Annotations\Annotation\BeforeAnnotation;
use Jaxon\Annotations\Annotation\DataBagAnnotation;
use Jaxon\Annotations\Annotation\ExcludeAnnotation;
use Jaxon\Annotations\Annotation\UploadAnnotation;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationManager;

use function array_filter;
use function count;
use function is_a;

class AnnotationReader
{
    /**
     * @var AnnotationManager
     */
    protected $xManager;

    /**
     * The constructor
     *
     * @param AnnotationManager $xManager
     */
    public function __construct(AnnotationManager $xManager)
    {
        $this->xManager = $xManager;
        $this->xManager->registry['upload'] = UploadAnnotation::class;
        $this->xManager->registry['databag'] = DataBagAnnotation::class;
        $this->xManager->registry['exclude'] = ExcludeAnnotation::class;
        $this->xManager->registry['before'] = BeforeAnnotation::class;;
        $this->xManager->registry['after'] = AfterAnnotation::class;;
        // Missing standard annotations.
        // We need to define this, otherwise they throw an exception, and make the whole processing fail.
        $this->xManager->registry['const'] = false;
        $this->xManager->registry['inheritDoc'] = false;
    }

    /**
     * @param array $aAnnotations
     *
     * @return AbstractAnnotation[]
     */
    private function filterAnnotations(array $aAnnotations): array
    {
        // Only keep the annotations declared in this package.
        $aAnnotations = array_filter($aAnnotations, function($xAnnotation) {
            return is_a($xAnnotation, AbstractAnnotation::class);
        });

        $aAttributes = [];
        foreach($aAnnotations as $xAnnotation)
        {
            $sName = $xAnnotation->getName();
            if(isset($aAttributes[$sName]))
            {
                $xAnnotation->setPrevValue($aAttributes[$sName]);
            }
            $xValue = $xAnnotation->getValue();
            if($sName === 'protected' && !$xValue)
            {
                continue; // Ignore annotation @exclude with value false
            }
            $aAttributes[$sName] = $xValue;
        }
        return $aAttributes;
    }

    /**
     * Get the class attributes from its annotations
     *
     * @param string $sClass
     * @param array $aMethods
     *
     * @return array
     * @throws AnnotationException
     */
    public function getAttributes(string $sClass, array $aMethods): array
    {
        // Class annotations
        $aClassAttrs = $this->filterAnnotations($this->xManager->getClassAnnotations($sClass));
        if(isset($aClassAttrs['protected']))
        {
            return [true, [], []]; // The entire class is not to be exported.
        }

        $aProtected = [];
        $aAttributes = [];
        if(count($aClassAttrs) > 0)
        {
            $aAttributes['*'] = $aClassAttrs;
        }

        // Methods annotations
        foreach($aMethods as $sMethod)
        {
            $aMethodAttrs = $this->filterAnnotations($this->xManager->getMethodAnnotations($sClass, $sMethod));
            if(isset($aMethodAttrs['protected']))
            {
                $aProtected[] = $sMethod; // The method is not to be exported.
            }
            elseif(count($aMethodAttrs) > 0)
            {
                $aAttributes[$sMethod] = $aMethodAttrs;
            }
        }
        return [false, $aAttributes, $aProtected];
    }
}
