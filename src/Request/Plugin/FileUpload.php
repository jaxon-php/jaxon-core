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
use Jaxon\Request\Support\FileUpload as Support;
use Jaxon\Response\UploadResponse;
use Exception;
use Closure;

class FileUpload extends RequestPlugin
{
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Validator;
    use \Jaxon\Features\Translator;

    /**
     * The uploaded files copied in the user dir
     *
     * @var array
     */
    protected $aUserFiles = [];

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
     * Is the current request an HTTP upload
     *
     * @var boolean
     */
    protected $bRequestIsHttpUpload = false;

    /**
     * HTTP file upload support
     *
     * @var Support
     */
    protected $xSupport = null;

    /**
     * The constructor
     *
     * @param Support       $xSupport       HTTP file upload support
     */
    public function __construct(Support $xSupport)
    {
        $this->xSupport = $xSupport;
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
     * @inheritDoc
     */
    public function getName()
    {
        return Jaxon::FILE_UPLOAD;
    }

    /**
     * Set the uploaded file name sanitizer
     *
     * @param Closure       $cSanitizer            The closure
     *
     * @return void
     */
    public function sanitizer(Closure $cSanitizer)
    {
        $this->xSupport->setNameSanitizer($cSanitizer);
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files()
    {
        return $this->aUserFiles;
    }

    /**
     * Make sure the upload dir exists and is writable
     *
     * @param string        $sUploadDir             The filename
     * @param string        $sUploadSubDir          The filename
     *
     * @return string
     */
    private function _makeUploadDir($sUploadDir, $sUploadSubDir)
    {
        $sUploadDir = rtrim(trim($sUploadDir), '/\\') . DIRECTORY_SEPARATOR;
        // Verify that the upload dir exists and is writable
        if(!is_writable($sUploadDir))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
        $sUploadDir .= $sUploadSubDir;
        if(!file_exists($sUploadDir) && !@mkdir($sUploadDir))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
        }
        return $sUploadDir;
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

        return $this->_makeUploadDir($sUploadDir, $this->sUploadSubdir);
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

        return $this->_makeUploadDir($sUploadDir, 'tmp' . DIRECTORY_SEPARATOR);
    }

    /**
     * Get the path to the upload temp file
     *
     * @return string
     */
    protected function getUploadTempFile()
    {
        $sUploadDir = $this->getOption('upload.default.dir');
        $sUploadDir = rtrim(trim($sUploadDir), '/\\') . DIRECTORY_SEPARATOR;
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
        $aTempFiles = $this->xSupport->getUploadedFiles();

        // Copy the uploaded files from the temp dir to the user dir
        foreach($aTempFiles as $sVarName => $aFiles)
        {
            $this->aUserFiles[$sVarName] = [];
            // Get the path to the upload dir
            $sUploadDir = $this->getUploadDir($sVarName);

            foreach($aFiles as $aFile)
            {
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
     * Inform this plugin that other plugin can process the current request
     *
     * @return void
     */
    public function noRequestPluginFound()
    {
        if(count($_FILES) > 0)
        {
            $this->bRequestIsHttpUpload = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function canProcessRequest()
    {
        return (count($_FILES) > 0 || ($this->sTempFile));
    }

    /**
     * Process the uploaded files in the HTTP request
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
            // Ajax or Http request with upload
            $this->readFromHttpData();

            if($this->bRequestIsHttpUpload)
            {
                // Process an HTTP upload request
                // This requires to set the response to be returned.
                $xResponse = new UploadResponse();
                try
                {
                    $this->saveToTempFile();
                    $xResponse->setUploadedFile($this->sTempFile);
                }
                catch(Exception $e)
                {
                    $xResponse->setErrorMessage($e->getMessage());
                }
                jaxon()->di()->getResponseManager()->append($xResponse);
            }
        }
        elseif(($this->sTempFile))
        {
            // Ajax request following and HTTP upload
            $this->readFromTempFile();
        }

        return true;
    }
}
