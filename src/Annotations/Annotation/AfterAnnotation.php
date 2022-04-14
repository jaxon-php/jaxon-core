<?php

/**
 * AfterAnnotation.php
 *
 * Jaxon annotation for callbacks.
 *
 * @package jaxon-core
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\AnnotationException;

/**
 * Specifies a method to be called after the one targeted by a Jaxon request.
 *
 * @usage('method'=>true, 'multiple'=>true, 'inherited'=>true)
 */
class AfterAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected $sMethodName = '';

    /**
     * @var array
     */
    protected $sMethodParams = [];

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(!isset($properties['name']) || !is_string($properties['name']))
        {
            throw new AnnotationException('The @after annotation requires a property "name" of type string');
        }
        foreach(array_keys($properties) as $propName)
        {
            if($propName !== 'name' && $propName !== 'params')
            {
                throw new AnnotationException('Unknown property "' . $propName . '" in the @after annotation');
            }
        }
        if(isset($properties['params']))
        {
            if(!is_array($properties['params']))
            {
                throw new AnnotationException('The "params" property of the @after annotation must be of type array');
            }
            $this->sMethodParams = $properties['params'];
        }
        $this->sMethodName = $properties['name'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '__after';
    }

    /**
     * @inheritDoc
     */
    public function getValue($xCurrValue)
    {
        return [$this->sMethodName => $this->sMethodParams];
    }
}
