<?php

/**
 * UploadedFile.php - This class represents an uploaded file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Support;

class UploadedFile
{
    /**
     * The uploaded file type
     *
     * @var string
     */
    protected $sType;

    /**
     * The uploaded file name, without the extension and slugified
     *
     * @var string
     */
    protected $sName;

    /**
     * The uploaded file name, with the extension
     *
     * @var string
     */
    protected $sFullname;

    /**
     * The uploaded file path
     *
     * @var string
     */
    protected $sPath;

    /**
     * The uploaded file size
     *
     * @var string
     */
    protected $sSize;

    /**
     * The uploaded file extension
     *
     * @var string
     */
    protected $sExtension;

    public function __construct($sUploadDir, array $aFile)
    {
        $this->sType = $aFile['type'];
        $this->sName = $this->slugify($aFile['filename']);
        $this->sExtension = $aFile['extension'];
        $this->sFullname = $aFile['name'];
        $this->sSize = $aFile['size'];
        $this->sPath = $sUploadDir . $this->sName . '.' . $this->sExtension;
    }

    /**
     * Slugify a text
     *
     * @var string
     */
    protected function slugify($sText)
    {
        // Todo: slugify the text.
        return $sText;
    }

    /**
     * Get the uploaded file type
     *
     * @return string
     */
    public function type()
    {
        return $this->sType;
    }

    /**
     * Get the uploaded file name, without the extension and slugified
     *
     * @return string
     */
    public function name()
    {
        return $this->sName;
    }

    /**
     * Get the uploaded file name, with the extension
     *
     * @return string
     */
    public function fullname()
    {
        return $this->sFullname;
    }

    /**
     * Get the uploaded file path
     *
     * @return string
     */
    public function path()
    {
        return $this->sPath;
    }

    /**
     * Get the uploaded file size
     *
     * @return string
     */
    public function size()
    {
        return $this->sSize;
    }
    
    /**
     * Get the uploaded file extension
     *
     * @return string
     */
    public function extension()
    {
        return $this->sExtension;
    }
}
