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
    protected $sDataFile = '';

    /**
     * The uploaded files received in the temp dir
     *
     * @var array
     */
    protected $aTempFiles;

    /**
     * Read uploaded files info from the $_FILES global var
     */
    public function __construct()
    {
        $this->aUserFiles = [];
        $this->aTempFiles = [];

        if(count($_FILES) > 0)
        {
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
                        if(!array_key_exists($sVarName, $this->aTempFiles))
                        {
                            $this->aTempFiles[$sVarName] = [];
                        }
                        // Copy the file data into the local array
                        $this->aTempFiles[$sVarName][] = [
                            'name' => $aFile['name'][$i],
                            'type' => $aFile['type'][$i],
                            'tmp_name' => $aFile['tmp_name'][$i],
                            'error' => $aFile['error'][$i],
                            'size' => $aFile['size'][$i],
                            'filename' => pathinfo($aFile['name'][$i], PATHINFO_FILENAME), // without the extension
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
                    if(!array_key_exists($sVarName, $this->aTempFiles))
                    {
                        $this->aTempFiles[$sVarName] = [];
                    }
                    // Copy the file data into the local array
                    $this->aTempFiles[$sVarName][] = [
                        'name' => $aFile['name'],
                        'type' => $aFile['type'],
                        'tmp_name' => $aFile['tmp_name'],
                        'error' => $aFile['error'],
                        'size' => $aFile['size'],
                        'filename' => pathinfo($aFile['name'], PATHINFO_FILENAME), // without the extension
                        'extension' => pathinfo($aFile['name'], PATHINFO_EXTENSION),
                    ];
                }
            }
        }
        else if(array_key_exists('jxnupl', $_POST))
        {
            $this->sDataFile = $_POST['jxnupl'];
        }
        else if(array_key_exists('jxnupl', $_GET))
        {
            $this->sDataFile = $_GET['jxnupl'];
        }
    }

    /**
     * Save uploaded files info to a temp file
     *
     * @return void
     */
    protected function saveToTempFile()
    {
        // Default upload dir
        $sUploadDir = $this->getOption('upload.default.dir');
        $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if(!is_writable($sUploadDir))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
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
        $this->sDataFile = uniqid();
        $sUploadDir .= 'tmp' . DIRECTORY_SEPARATOR;
        @mkdir($sUploadDir);
        file_put_contents($sUploadDir . $this->sDataFile . '.json', json_encode($aFiles));
    }

    /**
     * Read uploaded files info from a temp file
     *
     * @return void
     */
    protected function readFromTempFile()
    {
        // Default upload dir
        $sUploadDir = $this->getOption('upload.default.dir');
        $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $sUploadDir .= 'tmp' . DIRECTORY_SEPARATOR;
        if(!is_readable($sUploadDir . $this->sDataFile . '.json'))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
        $aFiles = file_get_contents($sUploadDir . $this->sDataFile . '.json');
        $aFiles = json_decode($aFiles, true);
        foreach($aFiles as $sVarName => $aUserFiles)
        {
            $this->aUserFiles[$sVarName] = [];
            foreach($aUserFiles as $aUserFile)
            {
                $this->aUserFiles[$sVarName][] = UploadedFile::fromTempData($aUserFile);
            }
        }
        unlink($sUploadDir . $this->sDataFile . '.json');
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @return void
     */
    protected function readFromHttpData()
    {
        // Default upload dir
        $sDefaultUploadDir = $this->getOption('upload.default.dir');
        // Check validity of the uploaded files
        foreach($this->aTempFiles as $sVarName => $aTempFiles)
        {
            $this->aUserFiles[$sVarName] = [];
            foreach($aTempFiles as $aFile)
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
                // Verify that the upload dir exists and is writable
                $sUploadDir = $this->getOption('upload.files.' . $sVarName . '.dir', $sDefaultUploadDir);
                $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if(!is_writable($sUploadDir))
                {
                    throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
                }
                // Set the user file data
                $this->aUserFiles[$sVarName][] = UploadedFile::fromHttpData($sUploadDir, $aFile);
            }
        }
        // Copy the uploaded files from the temp dir to the user dir
        foreach($this->aTempFiles as $sVarName => $aTempFiles)
        {
            for($i = 0; $i < count($aTempFiles); $i++)
            {
                // All's right, move the file to the user dir.
                move_uploaded_file($aTempFiles[$i]["tmp_name"], $this->aUserFiles[$sVarName][$i]->path());
            }
        }
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
        return (count($this->aTempFiles) > 0 || ($this->sDataFile));
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
        if(count($this->aTempFiles) > 0)
        {
            $this->readFromHttpData();
        }
        else if(($this->sDataFile))
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
        return $this->sDataFile;
    }
}
