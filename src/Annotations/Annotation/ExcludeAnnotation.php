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

/**
 * Specifies a method to be called after the one targeted by a Jaxon request.
 *
 * @usage('method'=>true, 'inherited'=>true)
 */
class ExcludeAnnotation extends AbstractAnnotation
{
    /**
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
            throw new AnnotationException('the @exclude annotation does not have any property');
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
    public function getValue($xCurrValue)
    {
        return $this->bValue;
    }
}
