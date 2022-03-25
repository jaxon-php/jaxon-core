<?php

/**
 * File.php - This class represents an uploaded file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Upload;

use Nyholm\Psr7\UploadedFile;

use Closure;

use function call_user_func_array;
use function pathinfo;

class File
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
     * A user defined function to transform uploaded file names
     *
     * @var Closure
     */
    protected static $cNameSanitizer = null;

    /**
     * Filter uploaded file name
     *
     * @param Closure $cNameSanitizer    The closure which filters filenames
     *
     * @return void
     */
    public static function setNameSanitizer(Closure $cNameSanitizer)
    {
        self::$cNameSanitizer = $cNameSanitizer;
    }

    /**
     * Slugify a text
     *
     * @param string  $sText
     *
     * @return string
     */
    protected static function slugify(string $sText): string
    {
        // Todo: slugify the text.
        return $sText;
    }

    /**
     * Create an instance of this class using data from an uploaded file.
     *
     * @param string $sVarName
     * @param string $sUploadDir    The directory where to save the uploaded file
     * @param UploadedFile $xHttpFile    The uploaded file
     *
     * @return File
     */
    public static function fromHttpFile(string $sVarName, string $sUploadDir, UploadedFile $xHttpFile): File
    {
        // Filename without the extension
        $sName = pathinfo($xHttpFile->getClientFilename(), PATHINFO_FILENAME);
        if(self::$cNameSanitizer !== null)
        {
            $sName = (string)call_user_func_array(self::$cNameSanitizer, [$sName, $sVarName]);
        }

        $xFile = new File();
        $xFile->sType = $xHttpFile->getClientMediaType();
        $xFile->sName = self::slugify($sName);
        $xFile->sFilename = $xHttpFile->getClientFilename();
        $xFile->sExtension = pathinfo($xHttpFile->getClientFilename(), PATHINFO_EXTENSION);
        $xFile->sSize = $xHttpFile->getSize();
        $xFile->sPath = $sUploadDir . $xFile->sName . '.' . $xFile->sExtension;
        return $xFile;
    }

    /**
     * Create an instance of this class using data from a temp file
     *
     * @param array $aFile    The uploaded file data
     *
     * @return File
     */
    public static function fromTempFile(array $aFile): File
    {
        $xFile = new File();
        $xFile->sType = $aFile['type'];
        $xFile->sName = $aFile['name'];
        $xFile->sFilename = $aFile['filename'];
        $xFile->sExtension = $aFile['extension'];
        $xFile->sSize = $aFile['size'];
        $xFile->sPath = $aFile['path'];
        return $xFile;
    }

    /**
     * Convert the File instance to array.
     *
     * @return array<string,string>
     */
    public function toTempData(): array
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
     * Get the uploaded file type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->sType;
    }

    /**
     * Get the uploaded file name, without the extension and slugified
     *
     * @return string
     */
    public function name(): string
    {
        return $this->sName;
    }

    /**
     * Get the uploaded file name, with the extension
     *
     * @return string
     */
    public function filename(): string
    {
        return $this->sFilename;
    }

    /**
     * Get the uploaded file path
     *
     * @return string
     */
    public function path(): string
    {
        return $this->sPath;
    }

    /**
     * Get the uploaded file size
     *
     * @return string
     */
    public function size(): string
    {
        return $this->sSize;
    }

    /**
     * Get the uploaded file extension
     *
     * @return string
     */
    public function extension(): string
    {
        return $this->sExtension;
    }
}
