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

use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Request\Support\UploadedFile;
use Jaxon\Request\Support\FileUpload as Support;
use Jaxon\Response\UploadResponse;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;
use Exception;
use Closure;

use function rtrim;
use function trim;
use function count;
use function is_writable;
use function is_readable;
use function file_exists;
use function mkdir;
use function move_uploaded_file;
use function uniqid;
use function file_put_contents;
use function json_decode;
use function file_get_contents;
use function unlink;

class FileUpload extends RequestPlugin
{
    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * The response manager
     *
     * @var ResponseManager
     */
    protected $xResponseManager;

    /**
     * HTTP file upload support
     *
     * @var Support
     */
    protected $xSupport = null;

    /**
     * @var Translator
     */
    protected $xTranslator;

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
     * @var bool
     */
    protected $bRequestIsHttpUpload = false;

    /**
     * The constructor
     *
     * @param Config  $xConfig
     * @param ResponseManager  $xResponseManager
     * @param Support $xSupport    HTTP file upload support
     * @param Translator  $xTranslator
     */
    public function __construct(Config $xConfig, ResponseManager $xResponseManager,
        Support $xSupport, Translator $xTranslator)
    {
        $this->xConfig = $xConfig;
        $this->xResponseManager = $xResponseManager;
        $this->xSupport = $xSupport;
        $this->xTranslator = $xTranslator;
        $this->sUploadSubdir = uniqid() . DIRECTORY_SEPARATOR;

        if(isset($_POST['jxnupl']))
        {
            $this->sTempFile = $_POST['jxnupl'];
        }
        elseif(isset($_GET['jxnupl']))
        {
            $this->sTempFile = $_GET['jxnupl'];
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Jaxon::FILE_UPLOAD;
    }

    /**
     * @inheritDoc
     */
    public function checkOptions(string $sCallable, $xOptions): array
    {
        return [];
    }

    /**
     * Set the uploaded file name sanitizer
     *
     * @param Closure $cSanitizer    The closure
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
    public function files(): array
    {
        return $this->aUserFiles;
    }

    /**
     * Make sure the upload dir exists and is writable
     *
     * @param string $sUploadDir    The filename
     * @param string $sUploadSubDir    The filename
     *
     * @return string
     * @throws SetupException
     */
    private function _makeUploadDir(string $sUploadDir, string $sUploadSubDir): string
    {
        $sUploadDir = rtrim(trim($sUploadDir), '/\\') . DIRECTORY_SEPARATOR;
        // Verify that the upload dir exists and is writable
        if(!is_writable($sUploadDir))
        {
            throw new SetupException($this->xTranslator->trans('errors.upload.access'));
        }
        $sUploadDir .= $sUploadSubDir;
        if(!file_exists($sUploadDir) && !@mkdir($sUploadDir))
        {
            throw new SetupException($this->xTranslator->trans('errors.upload.access'));
        }
        return $sUploadDir;
    }

    /**
     * Get the path to the upload dir
     *
     * @param string $sFieldId    The filename
     *
     * @return string
     * @throws SetupException
     */
    protected function getUploadDir(string $sFieldId): string
    {
        // Default upload dir
        $sDefaultUploadDir = $this->xConfig->getOption('upload.default.dir');
        $sUploadDir = $this->xConfig->getOption('upload.files.' . $sFieldId . '.dir', $sDefaultUploadDir);

        return $this->_makeUploadDir($sUploadDir, $this->sUploadSubdir);
    }

    /**
     * Get the path to the upload temp dir
     *
     * @return string
     * @throws SetupException
     */
    protected function getUploadTempDir(): string
    {
        // Default upload dir
        $sUploadDir = $this->xConfig->getOption('upload.default.dir');

        return $this->_makeUploadDir($sUploadDir, 'tmp' . DIRECTORY_SEPARATOR);
    }

    /**
     * Get the path to the upload temp file
     *
     * @return string
     * @throws SetupException
     */
    protected function getUploadTempFile(): string
    {
        $sUploadDir = $this->xConfig->getOption('upload.default.dir');
        $sUploadDir = rtrim(trim($sUploadDir), '/\\') . DIRECTORY_SEPARATOR;
        $sUploadDir .= 'tmp' . DIRECTORY_SEPARATOR;
        $sUploadTempFile = $sUploadDir . $this->sTempFile . '.json';
        if(!is_readable($sUploadTempFile))
        {
            throw new SetupException($this->xTranslator->trans('errors.upload.access'));
        }
        return $sUploadTempFile;
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @return void
     * @throws SetupException
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
     * @throws SetupException
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
     * @throws SetupException
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
    public function canProcessRequest(): bool
    {
        return (count($_FILES) > 0 || ($this->sTempFile));
    }

    /**
     * Process the uploaded files in the HTTP request
     *
     * @return bool
     * @throws SetupException
     */
    public function processRequest(): bool
    {
        if(!$this->canProcessRequest())
        {
            return false;
        }

        if(($this->sTempFile))
        {
            // Ajax request following and HTTP upload
            $this->readFromTempFile();
            return true;
        }

        if(count($_FILES) === 0)
        {
            return false;
        }
        // Ajax or Http request with upload
        $this->readFromHttpData();
        if(!$this->bRequestIsHttpUpload)
        {
            return false;
        }
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
        $this->xResponseManager->append($xResponse);
        return true;
    }
}
