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
    protected $sFilename;

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

    /**
     * Create an instance of this class using data from the $_FILES global var.
     *
     * @param string        $sUploadDir     The directory where to save the uploaded file
     * @param array         $aFile          The uploaded file data
     *
     * @return UploadedFile
     */
    public static function fromHttpData($sUploadDir, array $aFile)
    {
        $xFile = new UploadedFile();
        $xFile->sType = $aFile['type'];
        $xFile->sName = $xFile->slugify($aFile['filename']);
        $xFile->sFilename = $aFile['name'];
        $xFile->sExtension = $aFile['extension'];
        $xFile->sSize = $aFile['size'];
        $xFile->sPath = $sUploadDir . $xFile->sName . '.' . $xFile->sExtension;
        return $xFile;
    }

    /**
     * Convert the UploadedFile instance to array.
     *
     * @return array
     */
    public function toTempData()
    {
        return [
            'type' => $this->sType,
            'name' => $this->sName,
            'filename' => $this->sFilename,
            'extension' => $this->sExtension,
            'size' => $this->sSize,
            'path' => $this->sPath,
        ];
    }

    /**
     * Create an instance of this class using data from an array.
     *
     * @param array         $aFile          The uploaded file data
     *
     * @return UploadedFile
     */
    public static function fromTempData(array $aFile)
    {
        $xFile = new UploadedFile();
        $xFile->sType = $aFile['type'];
        $xFile->sName = $aFile['name'];
        $xFile->sFilename = $aFile['filename'];
        $xFile->sExtension = $aFile['extension'];
        $xFile->sSize = $aFile['size'];
        $xFile->sPath = $aFile['path'];
        return $xFile;
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
    public function filename()
    {
        return $this->sFilename;
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
