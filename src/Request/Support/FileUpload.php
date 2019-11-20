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

use Closure;

class FileUpload
{
    use \Jaxon\Features\Validator;
    use \Jaxon\Features\Translator;

    /**
     * A user defined function to transform uploaded file names
     *
     * @var Closure
     */
    protected $cNameSanitizer = null;

    /**
     * Filter uploaded file name
     *
     * @param Closure       $cNameSanitizer            The closure which filters filenames
     *
     * @return void
     */
    public function setNameSanitizer(Closure $cNameSanitizer)
    {
        $this->cNameSanitizer = $cNameSanitizer;
    }

    /**
     * Read uploaded files info from HTTP request data
     *
     * @return void
     */
    public function getUploadedFiles()
    {
        // Check validity of the uploaded files
        $aTempFiles = [];
        foreach($_FILES as $sVarName => $aFile)
        {
            // If there is only one file, transform each entry into an array,
            // so the same processing for multiple files can be applied.
            if(!is_array($aFile['name']))
            {
                $aFile['name'] = [$aFile['name']];
                $aFile['type'] = [$aFile['type']];
                $aFile['tmp_name'] = [$aFile['tmp_name']];
                $aFile['error'] = [$aFile['error']];
                $aFile['size'] = [$aFile['size']];
            }

            $nFileCount = count($aFile['name']);
            for($i = 0; $i < $nFileCount; $i++)
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
                $sFilename = pathinfo($aFile['name'][$i], PATHINFO_FILENAME);
                if(($this->cNameSanitizer))
                {
                    $sFilename = (string)call_user_func_array($$this->cNameSanitizer, [$sFilename, $sVarName]);
                }
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
           }
        }

        return $aTempFiles;
    }
}
