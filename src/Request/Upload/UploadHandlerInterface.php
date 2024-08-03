<?php

/**
 * UploadHandlerInterface.php
 *
 * Interface for the file upload handler.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Upload;

use Psr\Http\Message\ServerRequestInterface;

use Closure;

interface UploadHandlerInterface
{
    /**
     * Set the uploaded file name sanitizer
     *
     * @param Closure $cSanitizer    The closure
     *
     * @return void
     */
    public function sanitizer(Closure $cSanitizer);

    /**
     * Get the uploaded files
     *
     * @return FileInterface[]
     */
    public function files(): array;

    /**
     * Check if this is an HTTP (and not Ajax) upload
     *
     * @return void
     */
    public function isHttpUpload();

    /**
     * Check if the current request contains uploaded files
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return bool
     */
    public function canProcessRequest(ServerRequestInterface $xRequest): bool;

    /**
     * Process the uploaded files in the HTTP request
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return bool
     */
    public function processRequest(ServerRequestInterface $xRequest): bool;
}
