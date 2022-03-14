<?php

/**
 * UploadPlugin.php - This class handles HTTP file upload.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Plugin\Upload;

use Jaxon\Request\Validator;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\RequestException;

use Closure;
use Exception;

use function count;
use function rtrim;
use function trim;
use function substr;
use function str_shuffle;
use function is_string;
use function is_array;
use function is_dir;
use function mkdir;
use function is_readable;
use function is_writable;
use function json_encode;
use function json_decode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function move_uploaded_file;
use function unlink;
use function pathinfo;
use function random_bytes;
use function bin2hex;
use function call_user_func_array;

class Manager
{
    /**
     * @var Config
     */
    protected $xConfig;

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
     * A user defined function to transform uploaded file names
     *
     * @var Closure
     */
    protected $cNameSanitizer = null;

    /**
     * The subdir where uploaded files are stored
     *
     * @var string
     */
    protected $sUploadSubdir = '';

    /**
     * The constructor
     *
     * @param Config $xConfig
     * @param Validator $xValidator
     * @param Translator $xTranslator
     */
    public function __construct(Config $xConfig, Validator $xValidator, Translator $xTranslator)
    {
        $this->xConfig = $xConfig;
        $this->xValidator = $xValidator;
        $this->xTranslator = $xTranslator;
        $this->sUploadSubdir = $this->randomName() . DIRECTORY_SEPARATOR;
    }

    /**
     * Generate a random name
     *
     * @return string
     */
    protected function randomName(): string
    {
        try
        {
            return bin2hex(random_bytes(7));
        }
        catch(Exception $e){}
        // Generate the name
        $sChars = '0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($sChars), 0, 14);
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
     * Check uploaded files
     *
     * @param array $aFiles    The uploaded files
     *
     * @return void
     * @throws RequestException
     */
    private function checkFiles(array $aFiles)
    {
        foreach($aFiles as $sVarName => $aVarFiles)
        {
            foreach($aVarFiles as $aFile)
            {
                // Verify upload result
                if($aFile['error'] != 0)
                {
                    throw new RequestException($this->xTranslator->trans('errors.upload.failed', $aFile));
                }
                // Verify file validity (format, size)
                if(!$this->xValidator->validateUploadedFile($sVarName, $aFile))
                {
                    throw new RequestException($this->xValidator->getErrorMessage());
                }
            }
        }
    }

    /**
     * Get a file from upload entry
     *
     * @param string $sVarName    The corresponding variable
     * @param array $aVarFiles    An entry in the PHP $_FILES array
     * @param integer $nPosition    The position of the file to be processed
     *
     * @return null|array
     */
    private function getUploadedFile(string $sVarName, array $aVarFiles, int $nPosition): ?array
    {
        if(!$aVarFiles['name'][$nPosition])
        {
            return null;
        }

        // Filename without the extension
        $sFilename = pathinfo($aVarFiles['name'][$nPosition], PATHINFO_FILENAME);
        if(($this->cNameSanitizer))
        {
            $sFilename = (string)call_user_func_array($this->cNameSanitizer, [$sFilename, $sVarName]);
        }

        return [
            'name' => $aVarFiles['name'][$nPosition],
            'type' => $aVarFiles['type'][$nPosition],
            'tmp_name' => $aVarFiles['tmp_name'][$nPosition],
            'error' => $aVarFiles['error'][$nPosition],
            'size' => $aVarFiles['size'][$nPosition],
            'filename' => $sFilename,
            'extension' => pathinfo($aVarFiles['name'][$nPosition], PATHINFO_EXTENSION),
        ];
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @return array
     * @throws RequestException
     */
    public function getUploadedFiles(): array
    {
        // Check validity of the uploaded files
        $aUploadedFiles = [];
        foreach($_FILES as $sVarName => $aVarFiles)
        {
            // If there is only one file, transform each entry into an array,
            // so the same processing for multiple files can be applied.
            if(!is_array($aVarFiles['name']))
            {
                $aVarFiles['name'] = [$aVarFiles['name']];
                $aVarFiles['type'] = [$aVarFiles['type']];
                $aVarFiles['tmp_name'] = [$aVarFiles['tmp_name']];
                $aVarFiles['error'] = [$aVarFiles['error']];
                $aVarFiles['size'] = [$aVarFiles['size']];
            }

            $nFileCount = count($aVarFiles['name']);
            for($i = 0; $i < $nFileCount; $i++)
            {
                $aUploadedFile = $this->getUploadedFile($sVarName, $aVarFiles, $i);
                if(is_array($aUploadedFile))
                {
                    if(!isset($aUploadedFiles[$sVarName]))
                    {
                        $aUploadedFiles[$sVarName] = [];
                    }
                    $aUploadedFiles[$sVarName][] = $aUploadedFile;
                }
            }
        }

        // Check uploaded files validity
        $this->checkFiles($aUploadedFiles);

        return $aUploadedFiles;
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
        $sUploadDir = rtrim(trim($sUploadDir), '/\\') . DIRECTORY_SEPARATOR;
        // Verify that the upload dir exists and is writable
        if(!is_writable($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        $sUploadDir .= $sUploadSubDir;
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
        $sDefaultUploadDir = $this->xConfig->getOption('upload.default.dir');
        $sUploadDir = $this->xConfig->getOption('upload.files.' . $sFieldId . '.dir', $sDefaultUploadDir);
        if(!is_string($sUploadDir) || !is_dir($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $this->_makeUploadDir($sUploadDir, $this->sUploadSubdir);
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
        $sUploadDir = $this->xConfig->getOption('upload.default.dir');
        if(!is_string($sUploadDir) || !is_dir($sUploadDir))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $this->_makeUploadDir($sUploadDir, 'tmp' . DIRECTORY_SEPARATOR);
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
        $sUploadDir = $this->xConfig->getOption('upload.default.dir', '');
        $sUploadDir = rtrim(trim($sUploadDir), '/\\') . DIRECTORY_SEPARATOR;
        $sUploadDir .= 'tmp' . DIRECTORY_SEPARATOR;
        $sUploadTempFile = $sUploadDir . $sTempFile . '.json';
        if(!is_readable($sUploadTempFile))
        {
            throw new RequestException($this->xTranslator->trans('errors.upload.access'));
        }
        return $sUploadTempFile;
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @return array
     * @throws RequestException
     */
    public function readFromHttpData(): array
    {
        // Check validity of the uploaded files
        $aTempFiles = $this->getUploadedFiles();

        // Copy the uploaded files from the temp dir to the user dir
        $aUserFiles = [];
        foreach($aTempFiles as $sVarName => $aFiles)
        {
            $aUserFiles[$sVarName] = [];
            // Get the path to the upload dir
            $sUploadDir = $this->getUploadDir($sVarName);

            foreach($aFiles as $aFile)
            {
                // Set the user file data
                $xUploadedFile = File::fromHttpData($sUploadDir, $aFile);
                // All's right, move the file to the user dir.
                move_uploaded_file($aFile["tmp_name"], $xUploadedFile->path());
                $aUserFiles[$sVarName][] = $xUploadedFile;
            }
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
                $aUserFiles[$sVarName][] = File::fromTempData($aVarFile);
            }
        }
        @unlink($sUploadTempFile);
        return $aUserFiles;
    }
}
