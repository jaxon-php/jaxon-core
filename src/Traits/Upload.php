<?php

/**
 * Upload.php - Upload Trait
 *
 * The Jaxon class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Jaxon\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Jaxon class
 * as well as the client-side javascript.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Traits;

use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Manager as RequestManager;
use Jaxon\Response\Manager as ResponseManager;

use Jaxon\Utils\URI;
use Exception;
use Closure;

trait Upload
{
   /**
     * Check if uploaded files are available
     *
     * @return boolean
     */
    public function hasUploadedFiles()
    {
        if(($xUploadPlugin = $this->getPluginManager()->getRequestPlugin(self::FILE_UPLOAD)) == null)
        {
            return false;
        }
        return $xUploadPlugin->canProcessRequest();
    }

   /**
     * Check uploaded files validity and move them to the user dir
     *
     * @return boolean
     */
    public function saveUploadedFiles()
    {
        try
        {
            if(($xUploadPlugin = $this->getPluginManager()->getRequestPlugin(self::FILE_UPLOAD)) == null)
            {
                throw new Exception($this->trans('errors.upload.plugin'));
            }
            elseif(!$xUploadPlugin->canProcessRequest())
            {
                throw new Exception($this->trans('errors.upload.request'));
            }
            // Save uploaded files
            $sKey = $xUploadPlugin->saveUploadedFiles();
            $sResponse = '{"code": "success", "upl": "' . $sKey . '"}';
            $return = true;
        }
        catch(Exception $e)
        {
            $sResponse = '{"code": "error", "msg": "' . addslashes($e->getMessage()) . '"}';
            $return = false;
        }
        // Send the response back to the browser
        echo '<script>var res = ', $sResponse, '; </script>';
        if(($this->getOption('core.process.exit')))
        {
            exit();
        }
        return $return;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function getUploadedFiles()
    {
        if(($xUploadPlugin = $this->getPluginManager()->getRequestPlugin(self::FILE_UPLOAD)) == null)
        {
            return [];
        }
        return $xUploadPlugin->getUploadedFiles();
    }

    /**
     * Filter uploaded file name
     *
     * @param Closure       $fFileFilter            The closure which filters filenames
     *
     * @return void
     */
    public function setUploadFileFilter(Closure $fFileFilter)
    {
        if(($xUploadPlugin = $this->getPluginManager()->getRequestPlugin(self::FILE_UPLOAD)) == null)
        {
            return;
        }
        $xUploadPlugin->setFileFilter($fFileFilter);
    }
}
