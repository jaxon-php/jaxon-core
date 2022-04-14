<?php

/**
 * DataBagAnnotation.php
 *
 * Jaxon annotation for data bags.
 *
 * @package jaxon-core
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\AnnotationException;

use function count;
use function is_array;
use function is_string;

/**
 * Specifies a data bag stored in the browser and included in ajax requests to a method.
 *
 * @usage('method'=>true, 'multiple'=>true, 'inherited'=>true)
 */
class DataBagAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected $sName = '';

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) != 1 || !isset($properties['name']) || !is_string($properties['name']))
        {
            throw new AnnotationException('UploadAnnotation requires a name property of type string');
        }
        $this->sName = $properties['name'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'bags';
    }

    /**
     * @inheritDoc
     */
    public function getValue($xCurrValue)
    {
        if(!is_array($xCurrValue))
        {
            return [$this->sName];
        }
        $xCurrValue[] = $this->sName;
        return $xCurrValue;
    }
}
