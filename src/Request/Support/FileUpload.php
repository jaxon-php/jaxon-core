<?php

/**
 * FileUpload.php - This class handles HTTP file upload.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Support;

use Jaxon\Request\Validator;

use Closure;

class FileUpload
{
    use \Jaxon\Features\Translator;

    /**
     * The request data validator
     *
     * @var Validator
     */
    protected $xValidator;

    /**
     * A user defined function to transform uploaded file names
     *
     * @var Closure
     */
    protected $cNameSanitizer = null;

    /**
     * The constructor
     *
     * @param Validator         $xValidator
     */
    public function __construct(Validator $xValidator)
    {
        $this->xValidator = $xValidator;
    }

    /**
     * Filter uploaded file name
     *
     * @param Closure       $cNameSanitizer     The closure which filters filenames
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
     * @param array     $aFiles     The uploaded files
     *
     * @return void
     * @throws \Jaxon\Exception\SetupException
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
                    throw new \Jaxon\Exception\SetupException($this->trans('errors.upload.failed', $aFile));
                }
                // Verify file validity (format, size)
                if(!$this->xValidator->validateUploadedFile($sVarName, $aFile))
                {
                    throw new \Jaxon\Exception\SetupException($this->getValidatorMessage());
                }
            }
        }
    }

    /**
     * Get a file from upload entry
     *
     * @param string    $sVarName       The corresponding variable
     * @param array     $aVarFiles      An entry in the PHP $_FILES array
     * @param integer   $nPosition      The postion of the file to be processed
     *
     * @return null|array
     */
    private function getUploadedFile(string $sVarName, array $aVarFiles, int $nPosition)
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
     */
    public function getUploadedFiles()
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
}
