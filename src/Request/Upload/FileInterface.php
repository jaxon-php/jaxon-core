<?php

/**
 * FileInterface.php
 *
 * Interface for an uploaded file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Upload;

use League\Flysystem\Filesystem;

interface FileInterface
{
    /**
     * Get the filesystem where the file is stored
     *
     * @return Filesystem
     */
    public function filesystem(): Filesystem;

    /**
     * Get the uploaded file type
     *
     * @return string
     */
    public function type(): string;

    /**
     * Get the uploaded file name, without the extension and slugified
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the uploaded file name, with the extension
     *
     * @return string
     */
    public function filename(): string;

    /**
     * Get the uploaded file path
     *
     * @return string
     */
    public function path(): string;

    /**
     * Get the uploaded file size
     *
     * @return int
     */
    public function size(): int;

    /**
     * Get the uploaded file extension
     *
     * @return string
     */
    public function extension(): string;
}
