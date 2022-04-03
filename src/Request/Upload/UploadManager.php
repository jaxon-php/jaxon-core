<?php

/**
 * UploadManager.php - This class processes uploaded files.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Upload;

use Jaxon\Config\ConfigManager;
use Jaxon\Request\Validator;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\RequestException;
use Nyholm\Psr7\UploadedFile;
use Psr\Http\Message\ServerRequestInterface;

use Closure;

use function call_user_func;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_readable;
use function is_string;
use function is_writable;
use function json_decode;
use function json_encode;
use function mkdir;
use function realpath;
use function rtrim;
use function trim;
use function unlink;

class UploadManager
{
    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * The request data validator
     *
     * @var Validator
     */
    protected $xValidator;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The file and dir name generator
     *
     * @var NameGeneratorInterface
     */
    protected $xNameGenerator;

    /**
     * The id of the upload field in the form
     *
     * @var string
     */
    protected $sUploadFieldId = '';

    /**
     * A user defined function to transform uploaded file names
     *
     * @var Closure
     */
    protected $cNameSanitizer = null;

    /**
     * A flat list of all uploaded files
     *
     * @var array
     */
    private $aAllFiles = [];

    /**
     * The constructor
     *
     * @param NameGeneratorInterface $xNameGenerator
     * @param ConfigManager $xConfigManager
     * @param Validator $xValidator
     * @param Translator $xTranslator
     */
    public function __construct(NameGeneratorInterface $xNameGenerator, ConfigManager $xConfigManager,
        Validator $xValidator, Translator $xTranslator)
    {
        $this->xNameGenerator = $xNameGenerator;
        $this->xConfigManager = $xConfigManager;
        $this->xValidator = $xValidator;
        $this->xTranslator = $xTranslator;
        // This feature is not yet implemented
        $this->setUploadFieldId('');
    }

    /**
     * Generate a random name
     *
     * @return string
     */
    protected function randomName(): string
    {
        return $this->xNameGenerator->random(14);
    }

    /**
     * Set the id of the upload field in the form
     *
     * @param string $sUploadFieldId
     *
     * @return void
     */
    public function setUploadFieldId(string $sUploadFieldId)
    {
        $this->sUploadFieldId = $sUploadFieldId;
    }

    /**
     * Filter uploaded file name
     *
     * @param Closure $cNameSanitizer    The closure which filters filenames
     *
     * @return void
     */
    public function setNameSanitizer(Closure $cNameSanitizer)
    {
        $this->cNameSanitizer = $cNameSanitizer;
    }

    /**
     * Make sure the upload dir exists and is writable
     *
     * @param string $sUploadDir    The filename
     * @param string $sUploadSubDir    The filename
     *
     * @return string
     * @throws RequestException
     */
    private function _makeUploadDir(string $sUploadDir, string $sUploadSubDir): string
    {
        $sUploadDir = realpath(rtrim(trim($sUploadDir), '/\\'));
        // Verify that the upload dir exists and is writable
        if(!is_dir($sUploadDir) || !is_writable($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        $sUploadDir .= DIRECTORY_SEPARATOR . $sUploadSubDir . DIRECTORY_SEPARATOR;
        if(!file_exists($sUploadDir) && !@mkdir($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $sUploadDir;
    }

    /**
     * Get the path to the upload dir
     *
     * @param string $sFieldId    The filename
     *
     * @return string
     * @throws RequestException
     */
    protected function getUploadDir(string $sFieldId): string
    {
        // Default upload dir
        $sDefaultUploadDir = $this->xConfigManager->getOption('upload.default.dir');
        $sUploadDir = $this->xConfigManager->getOption('upload.files.' . $sFieldId . '.dir', $sDefaultUploadDir);
        if(!is_string($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $this->_makeUploadDir($sUploadDir, $this->randomName());
    }

    /**
     * Get the path to the upload temp dir
     *
     * @return string
     * @throws RequestException
     */
    protected function getUploadTempDir(): string
    {
        // Default upload dir
        $sUploadDir = $this->xConfigManager->getOption('upload.default.dir');
        if(!is_string($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $this->_makeUploadDir($sUploadDir, 'tmp');
    }

    /**
     * Check uploaded files
     *
     * @param string $sVarName
     * @param string $sUploadDir
     * @param UploadedFile $xHttpFile
     *
     * @return File
     * @throws RequestException
     */
    private function makeUploadedFile(string $sVarName, string $sUploadDir, UploadedFile $xHttpFile): File
    {
        // Filename without the extension. Needs to be sanitized.
        $sName = pathinfo($xHttpFile->getClientFilename(), PATHINFO_FILENAME);
        if($this->cNameSanitizer !== null)
        {
            $sName = (string)call_user_func($this->cNameSanitizer, $sName, $sVarName, $this->sUploadFieldId);
        }
        // Check the uploaded file validity
        if($xHttpFile->getError())
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.failed', ['name' => $sVarName]));
        }
        // Set the user file data
        $xFile = File::fromHttpFile($sName, $sUploadDir, $xHttpFile);
        // Verify file validity (format, size)
        if(!$this->xValidator->validateUploadedFile($sVarName, $xFile))
        {
            throw new RequestException($this->xValidator->getErrorMessage());
        }
        // All's right, save the file for copy.
        $this->aAllFiles[] = ['temp' => $xHttpFile, 'user' => $xFile];
        return $xFile;
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return array
     * @throws RequestException
     */
    public function readFromHttpData(ServerRequestInterface $xRequest): array
    {
        // Get the uploaded files
        $aTempFiles = $xRequest->getUploadedFiles();

        $aUserFiles = [];
        $this->aAllFiles = []; // A flat list of all uploaded files
        foreach($aTempFiles as $sVarName => $aFiles)
        {
            $aUserFiles[$sVarName] = [];
            // Get the path to the upload dir
            $sUploadDir = $this->getUploadDir($sVarName);
            if(!is_array($aFiles))
            {
                $aFiles = [$aFiles];
            }
            foreach($aFiles as $xHttpFile)
            {
                $aUserFiles[$sVarName][] = $this->makeUploadedFile($sVarName, $sUploadDir, $xHttpFile);
            }
        }
        // Copy the uploaded files from the temp dir to the user dir
        foreach($this->aAllFiles as $aFiles)
        {
            $aFiles['temp']->moveTo($aFiles['user']->path());
        }
        return $aUserFiles;
    }

    /**
     * Save uploaded files info to a temp file
     *
     * @param array $aUserFiles
     *
     * @return string
     * @throws RequestException
     */
    public function saveToTempFile(array $aUserFiles): string
    {
        // Convert uploaded file to an array
        $aFiles = [];
        foreach($aUserFiles as $sVarName => $aVarFiles)
        {
            $aFiles[$sVarName] = [];
            foreach($aVarFiles as $aVarFile)
            {
                $aFiles[$sVarName][] = $aVarFile->toTempData();
            }
        }
        // Save upload data in a temp file
        $sUploadDir = $this->getUploadTempDir();
        $sTempFile = $this->randomName();
        file_put_contents($sUploadDir . $sTempFile . '.json', json_encode($aFiles));
        return $sTempFile;
    }

    /**
     * Get the path to the upload temp file
     *
     * @param string $sTempFile
     *
     * @return string
     * @throws RequestException
     */
    protected function getUploadTempFile(string $sTempFile): string
    {
        // Verify file name validity
        if(!$this->xValidator->validateTempFileName($sTempFile))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.invalid'));
        }
        $sUploadDir = $this->xConfigManager->getOption('upload.default.dir', '');
        $sUploadDir = realpath(rtrim(trim($sUploadDir), '/\\')) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        $sUploadTempFile = $sUploadDir . $sTempFile . '.json';
        if(!is_readable($sUploadTempFile))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $sUploadTempFile;
    }

    /**
     * Read uploaded files info from a temp file
     *
     * @param string $sTempFile
     *
     * @return array
     * @throws RequestException
     */
    public function readFromTempFile(string $sTempFile): array
    {
        // Upload temp file
        $sUploadTempFile = $this->getUploadTempFile($sTempFile);
        $aFiles = json_decode(file_get_contents($sUploadTempFile), true);
        $aUserFiles = [];
        foreach($aFiles as $sVarName => $aVarFiles)
        {
            $aUserFiles[$sVarName] = [];
            foreach($aVarFiles as $aVarFile)
            {
                $aUserFiles[$sVarName][] = File::fromTempFile($aVarFile);
            }
        }
        @unlink($sUploadTempFile);
        return $aUserFiles;
    }
}
