<?php

/**
 * FileUpload.php - This class implements file upload with Ajax.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;
use Jaxon\Request\Support\UploadedFile;

use Closure;

class FileUpload extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The uploaded files copied in the user dir
     *
     * @var array
     */
    protected $aUserFiles;

    /**
     * The name of file containing upload data
     *
     * @var string
     */
    protected $sTempFile = '';

    /**
     * The subdir where uploaded files are stored
     *
     * @var string
     */
    protected $sUploadSubdir = '';

    /**
     * A user defined function to transform uploaded file names
     *
     * @var Closure
     */
    protected $fFileFilter = null;

    /**
     * Read uploaded files info from the $_FILES global var
     */
    public function __construct()
    {
        $this->aUserFiles = [];
        $this->sUploadSubdir = uniqid() . DIRECTORY_SEPARATOR;

        if(array_key_exists('jxnupl', $_POST))
        {
            $this->sTempFile = $_POST['jxnupl'];
        }
        elseif(array_key_exists('jxnupl', $_GET))
        {
            $this->sTempFile = $_GET['jxnupl'];
        }
    }

    /**
     * Filter uploaded file name
     *
     * @param Closure       $fFileFilter            The closure which filters filenames
     *
     * @return void
     */
    public function setFileFilter(Closure $fFileFilter)
    {
        $this->fFileFilter = $fFileFilter;
    }

    /**
     * Filter uploaded file name
     *
     * @param string        $sFilename              The filename
     * @param string        $sVarName               The associated variable name
     *
     * @return string
     */
    protected function filterFilename($sFilename, $sVarName)
    {
        if(($this->fFileFilter))
        {
            $fFileFilter = $this->fFileFilter;
            $sFilename = (string)$fFileFilter($sFilename, $sVarName);
        }
        return $sFilename;
    }

    /**
     * Get the path to the upload dir
     *
     * @param string        $sFieldId               The filename
     *
     * @return string
     */
    protected function getUploadDir($sFieldId)
    {
        // Default upload dir
        $sDefaultUploadDir = $this->getOption('upload.default.dir');
        $sUploadDir = $this->getOption('upload.files.' . $sFieldId . '.dir', $sDefaultUploadDir);
        $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        // Verify that the upload dir exists and is writable
        if(!is_writable($sUploadDir))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
        $sUploadDir .= $this->sUploadSubdir;
        @mkdir($sUploadDir);
        return $sUploadDir;
    }

    /**
     * Get the path to the upload temp dir
     *
     * @return string
     */
    protected function getUploadTempDir()
    {
        // Default upload dir
        $sUploadDir = $this->getOption('upload.default.dir');
        $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        // Verify that the upload dir exists and is writable
        if(!is_writable($sUploadDir))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
        $sUploadDir .= 'tmp' . DIRECTORY_SEPARATOR;
        @mkdir($sUploadDir);
        return $sUploadDir;
    }

    /**
     * Get the path to the upload temp file
     *
     * @return string
     */
    protected function getUploadTempFile()
    {
        $sUploadDir = $this->getOption('upload.default.dir');
        $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $sUploadDir .= 'tmp' . DIRECTORY_SEPARATOR;
        $sUploadTempFile = $sUploadDir . $this->sTempFile . '.json';
        if(!is_readable($sUploadTempFile))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
        return $sUploadTempFile;
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @return void
     */
    protected function readFromHttpData()
    {
        // Check validity of the uploaded files
        $aTempFiles = [];
        foreach($_FILES as $sVarName => $aFile)
        {
            if(is_array($aFile['name']))
            {
                for($i = 0; $i < count($aFile['name']); $i++)
                {
                    if(!$aFile['name'][$i])
                    {
                        continue;
                    }
                    if(!array_key_exists($sVarName, $aTempFiles))
                    {
                        $aTempFiles[$sVarName] = [];
                    }
                    // Filename without the extension
                    $sFilename = $this->filterFilename(pathinfo($aFile['name'][$i], PATHINFO_FILENAME), $sVarName);
                    // Copy the file data into the local array
                    $aTempFiles[$sVarName][] = [
                        'name' => $aFile['name'][$i],
                        'type' => $aFile['type'][$i],
                        'tmp_name' => $aFile['tmp_name'][$i],
                        'error' => $aFile['error'][$i],
                        'size' => $aFile['size'][$i],
                        'filename' => $sFilename,
                        'extension' => pathinfo($aFile['name'][$i], PATHINFO_EXTENSION),
                    ];
                }
            }
            else
            {
                if(!$aFile['name'])
                {
                    continue;
                }
                if(!array_key_exists($sVarName, $aTempFiles))
                {
                    $aTempFiles[$sVarName] = [];
                }
                // Filename without the extension
                $sFilename = $this->filterFilename(pathinfo($aFile['name'], PATHINFO_FILENAME), $sVarName);
                // Copy the file data into the local array
                $aTempFiles[$sVarName][] = [
                    'name' => $aFile['name'],
                    'type' => $aFile['type'],
                    'tmp_name' => $aFile['tmp_name'],
                    'error' => $aFile['error'],
                    'size' => $aFile['size'],
                    'filename' => $sFilename,
                    'extension' => pathinfo($aFile['name'], PATHINFO_EXTENSION),
                ];
            }
        }

        // Check uploaded files validity
        foreach($aTempFiles as $sVarName => $aFiles)
        {
            foreach($aFiles as $aFile)
            {
                // Verify upload result
                if($aFile['error'] != 0)
                {
                    throw new \Jaxon\Exception\Error($this->trans('errors.upload.failed', $aFile));
                }
                // Verify file validity (format, size)
                if(!$this->validateUploadedFile($sVarName, $aFile))
                {
                    throw new \Jaxon\Exception\Error($this->getValidatorMessage());
                }
                // Get the path to the upload dir
                $sUploadDir = $this->getUploadDir($sVarName);
            }
        }

        // Copy the uploaded files from the temp dir to the user dir
        foreach($aTempFiles as $sVarName => $_aTempFiles)
        {
            $this->aUserFiles[$sVarName] = [];
            foreach($_aTempFiles as $aFile)
            {
                // Get the path to the upload dir
                $sUploadDir = $this->getUploadDir($sVarName);
                // Set the user file data
                $xUploadedFile = UploadedFile::fromHttpData($sUploadDir, $aFile);
                // All's right, move the file to the user dir.
                move_uploaded_file($aFile["tmp_name"], $xUploadedFile->path());
                $this->aUserFiles[$sVarName][] = $xUploadedFile;
            }
        }
    }

    /**
     * Save uploaded files info to a temp file
     *
     * @return void
     */
    protected function saveToTempFile()
    {
        // Convert uploaded file to an array
        $aFiles = [];
        foreach($this->aUserFiles as $sVarName => $aUserFiles)
        {
            $aFiles[$sVarName] = [];
            foreach($aUserFiles as $aUserFile)
            {
                 $aFiles[$sVarName][] = $aUserFile->toTempData();
            }
        }
        // Save upload data in a temp file
        $sUploadDir = $this->getUploadTempDir();
        $this->sTempFile = uniqid();
        file_put_contents($sUploadDir . $this->sTempFile . '.json', json_encode($aFiles));
    }

    /**
     * Read uploaded files info from a temp file
     *
     * @return void
     */
    protected function readFromTempFile()
    {
        // Upload temp file
        $sUploadTempFile = $this->getUploadTempFile();
        $aFiles = json_decode(file_get_contents($sUploadTempFile), true);
        foreach($aFiles as $sVarName => $aUserFiles)
        {
            $this->aUserFiles[$sVarName] = [];
            foreach($aUserFiles as $aUserFile)
            {
                $this->aUserFiles[$sVarName][] = UploadedFile::fromTempData($aUserFile);
            }
        }
        unlink($sUploadTempFile);
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::FILE_UPLOAD;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->aUserFiles;
    }

    /**
     * Register a browser event
     *
     * @param array         $aArgs                An array containing the event specification
     *
     * @return \Jaxon\Request\Request
     */
    public function register($aArgs)
    {
        return false;
    }

    /**
     * Generate a hash for the registered browser events
     *
     * @return string
     */
    public function generateHash()
    {
        return '';
    }

    /**
     * Generate client side javascript code for the registered browser events
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        return (count($_FILES) > 0 || ($this->sTempFile));
    }

    /**
     * Process the uploaded files into the HTTP request
     *
     * @return boolean
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
        {
            return false;
        }
        if(count($_FILES) > 0)
        {
            $this->readFromHttpData();
        }
        elseif(($this->sTempFile))
        {
            $this->readFromTempFile();
        }
        return true;
    }

    /**
     * Check uploaded files validity and move them to the user dir
     *
     * @return boolean
     */
    public function saveUploadedFiles()
    {
        // Process uploaded files
        if(!$this->processRequest())
        {
            return '';
        }
        // Save upload data in a temp file
        $this->saveToTempFile();
        return $this->sTempFile;
    }
}
