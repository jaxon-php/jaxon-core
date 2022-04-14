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

use function array_keys;
use function is_array;
use function is_string;

/**
 * Specifies a method to be called after the one targeted by a Jaxon request.
 *
 * @usage('class' => true, 'method'=>true, 'multiple'=>true, 'inherited'=>true)
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
        if(!isset($properties['call']) || !is_string($properties['call']))
        {
            throw new AnnotationException('The @after annotation requires a property "call" of type string');
        }
        foreach(array_keys($properties) as $propName)
        {
            if($propName !== 'call' && $propName !== 'with')
            {
                throw new AnnotationException('Unknown property "' . $propName . '" in the @after annotation');
            }
        }
        if(isset($properties['with']))
        {
            if(!is_array($properties['with']))
            {
                throw new AnnotationException('The "with" property of the @after annotation must be of type array');
            }
            $this->sMethodParams = $properties['with'];
        }
        $this->sMethodName = $properties['call'];
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
    public function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            // Add the current value to the array
            $this->xPrevValue[$this->sMethodName] = $this->sMethodParams;
            return $this->xPrevValue;
        }
        // Return the current value in an array
        return [$this->sMethodName => $this->sMethodParams];
    }
}
