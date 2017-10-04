<?php

/**
 * FileUpload.php - This class handles Ajax uploaded files.
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
     * The uploaded files received in the temp dir
     *
     * @var array
     */
    protected $aFiles;

    public function __construct()
    {
        $this->aUserFiles = [];
        $this->aFiles = [];

        foreach($_FILES as $sVarName => $aFile)
        {
            $this->aFiles[$sVarName] = [];
            if(is_array($aFile['name']))
            {
                for($i = 0; $i < count($aFile['name']); $i++)
                {
                    // Copy the file data into the local array
                    $this->aFiles[$sVarName][] = [
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
                // Copy the file data into the local array
                $this->aFiles[$sVarName][] = [
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

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return 'FileUpload';
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
        // This plugin does not process a request all alone.
        // It provides complementary feature to other request plugins instead.
        return false;
    }

    /**
     * Process the uploaded files into the HTTP request
     *
     * @return boolean
     */
    public function processRequest()
    {
        // Default upload dir
        $sDefaultUploadDir = $this->getOption('upload.default.dir');
        // Check validity of the uploaded files
        foreach($this->aFiles as $sVarName => $aFiles)
        {
            $this->aUserFiles[$sVarName] = [];
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
                // Verify that the upload dir exists and is writable
                $sUploadDir = $this->getOption('upload.files.' . $sVarName . '.dir', $sDefaultUploadDir);
                $sUploadDir = rtrim(trim($sUploadDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if(!is_writable($sUploadDir))
                {
                    throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
                }
                // Set the user file data
                $this->aUserFiles[$sVarName][] = new UploadedFile($sUploadDir, $aFile);
            }
        }
        // Copy the uploaded files from the temp dir to the user dir
        foreach($this->aFiles as $sVarName => $aFiles)
        {
            for($i = 0; $i < count($aFiles); $i++)
            {
                // All's right, move the file to the user dir.
                move_uploaded_file($aFiles[$i]["tmp_name"], $this->aUserFiles[$sVarName][$i]->path());
            }
        }
        return true;
    }
}
