<?php

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\Annotation;

abstract class AbstractAnnotation extends Annotation
{
    /**
     * Get the annotation name
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get the annotation attribute value
     *
     * @param mixed $xCurrValue The current value of the attribute
     *
     * @return mixed
     */
    abstract public function getValue($xCurrValue);
}
