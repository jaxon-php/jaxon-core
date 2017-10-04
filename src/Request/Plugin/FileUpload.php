<?php

/**
 * FileUpload.php - Jaxon browser event
 *
 * This class adds server side event handling capabilities to Jaxon
 *
 * Events can be registered, then event handlers attached.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;

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

        foreach($_FILES as $var => $aFile)
        {
            $this->aFiles[$var] = [];
            if(is_array($aFile['name']))
            {
                for($i = 0; $i < count($aFile['name']); $i++)
                {
                    // Copy the file data into the local array
                    $this->aFiles[$var][] = [
                        'name' => $aFile['name'][$i],
                        'type' => $aFile['type'][$i],
                        'tmp_name' => $aFile['tmp_name'][$i],
                        'error' => $aFile['error'][$i],
                        'size' => $aFile['size'][$i],
                    ];
                }
            }
            else
            {
                // Copy the file data into the local array
                $this->aFiles[$var][] = [
                    'name' => $aFile['name'],
                    'type' => $aFile['type'],
                    'tmp_name' => $aFile['tmp_name'],
                    'error' => $aFile['error'],
                    'size' => $aFile['size'],
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
        $sDefaultUploadDir = $this->getOption('upload.dir');
        // Check validity of the uploaded files
        foreach($this->aFiles as $var => &$aFiles)
        {
            $this->aUserFiles[$var] = [];
            foreach($aFiles as &$aFile)
            {
                // Verify upload result
                if($aFile['error'] != 0)
                {
                    throw new \Jaxon\Exception\Error($this->trans('errors.upload.failed', $aFile));
                }
                // Verify file validity (format, size)
                if(!$this->validateUploadedFile($aFile))
                {
                    throw new \Jaxon\Exception\Error($this->trans('errors.upload.invalid', $aFile));
                }
                // Verify that the upload dir exists and is writable
                $sUploadDir = trim($this->getOption('upload.files.' . $var . '.dir', $sDefaultUploadDir));
                $sUploadDir = rtrim($sUploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if(!is_writable($sUploadDir))
                {
                    throw new \Jaxon\Exception\Error($this->trans('errors.upload.access'));
                }
                // Set the user file data
                $this->aUserFiles[$var][] = [
                    'type' => $aFile['type'],
                    'name' => $aFile['name'],
                    'path' => $sUploadDir . $aFile["name"],
                    'size' => $aFile['size'],
                ];
            }
        }
        // Copy the uploaded files from the temp dir to the user dir
        foreach($this->aFiles as $var => $aFiles)
        {
            for($i = 0; $i < count($aFiles); $i++)
            {
                // All's right, move the file to the user dir.
                move_uploaded_file($aFiles[$i]["tmp_name"], $this->aUserFiles[$var][$i]["path"]);
            }
        }
        return true;
    }
}
