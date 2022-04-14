<?php

/**
 * UploadAnnotation.php
 *
 * Jaxon annotation for file upload.
 *
 * @package jaxon-core
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\Annotation;
use mindplay\annotations\AnnotationException;

/**
 * Specifies an upload form field id.
 *
 * @usage('method'=>true, 'inherited'=>true)
 */
class UploadAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected $sField = '';

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) != 1 || !isset($properties['field']) || !is_string($properties['field']))
        {
            throw new AnnotationException('UploadAnnotation requires a field property of type string');
        }
        $this->sField = $properties['field'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'upload';
    }

    /**
     * @inheritDoc
     */
    public function getValue($xCurrValue)
    {
        return "'" . $this->sField . "'" ;
    }
}
