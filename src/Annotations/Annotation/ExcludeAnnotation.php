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

use function count;
use function is_bool;

/**
 * Specifies a method to be called after the one targeted by a Jaxon request.
 *
 * @usage('class' => true, 'method'=>true)
 */
class ExcludeAnnotation extends AbstractAnnotation
{
    /**
     * The name of the upload field
     *
     * @var bool
     */
    protected $bValue;

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) !== 0 &&
            (count($properties) !== 1 || !isset($properties[0]) || !is_bool($properties[0])))
        {
            throw new AnnotationException('the @exclude annotation requires a boolean or no property');
        }
        $this->bValue = $properties[0] ?? true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'protected';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->bValue;
    }
}
