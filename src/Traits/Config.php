<?php

/**
 * Config.php - Config Trait
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

use Jaxon\Utils\Config\Php;
use Jaxon\Utils\Config\Yaml;
use Jaxon\Utils\Config\Json;

trait Config
{
    /**
     * Set the default options of all components of the library
     *
     * @return void
     */
    private function setDefaultOptions()
    {
        // The default configuration settings.
        $this->setOptions(array(
            'core.version'                      => $this->getVersion(),
            'core.language'                     => 'en',
            'core.encoding'                     => 'utf-8',
            'core.decode_utf8'                  => false,
            'core.prefix.function'              => 'jaxon_',
            'core.prefix.class'                 => 'Jaxon',
            'core.prefix.event'                 => 'jaxon_event_',
            // 'core.request.uri'               => '',
            'core.request.mode'                 => 'asynchronous',
            'core.request.method'               => 'POST',    // W3C: Method is case sensitive
            'core.response.merge.ap'            => true,
            'core.response.merge.js'            => true,
            'core.debug.on'                     => false,
            'core.debug.verbose'                => false,
            'core.process.exit'                 => true,
            'core.process.clean'                => false,
            'core.process.timeout'              => 6000,
            'core.error.handle'                 => false,
            'core.error.log_file'               => '',
            'core.jquery.no_conflict'           => false,
            'js.lib.output_id'                  => 0,
            'js.lib.queue_size'                 => 0,
            'js.lib.load_timeout'               => 2000,
            'js.lib.show_status'                => false,
            'js.lib.show_cursor'                => true,
            'js.app.dir'                        => '',
            'js.app.minify'                     => true,
            'js.app.options'                    => '',
        ));
    }

    /**
     * Read and set Jaxon options from a PHP config file
     *
     * @param string        $sConfigFile        The full path to the config file
     * @param string        $sLibKey            The key of the library options in the file
     * @param string|null   $sAppKey            The key of the application options in the file
     *
     * @return array
     */
    public function readPhpConfigFile($sConfigFile, $sLibKey = '', $sAppKey = null)
    {
        return Php::read($sConfigFile, $sLibKey, $sAppKey);
    }

    /**
     * Read and set Jaxon options from a YAML config file
     *
     * @param string        $sConfigFile        The full path to the config file
     * @param string        $sLibKey            The key of the library options in the file
     * @param string|null   $sAppKey            The key of the application options in the file
     *
     * @return array
     */
    public function readYamlConfigFile($sConfigFile, $sLibKey = '', $sAppKey = null)
    {
        return Yaml::read($sConfigFile, $sLibKey, $sAppKey);
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param string        $sConfigFile        The full path to the config file
     * @param string        $sLibKey            The key of the library options in the file
     * @param string|null   $sAppKey            The key of the application options in the file
     *
     * @return array
     */
    public function readJsonConfigFile($sConfigFile, $sLibKey = '', $sAppKey = null)
    {
        return Json::read($sConfigFile, $sLibKey, $sAppKey);
    }

    /**
     * Read and set Jaxon options from a config file
     *
     * @param string        $sConfigFile        The full path to the config file
     * @param string        $sLibKey            The key of the library options in the file
     * @param string|null   $sAppKey            The key of the application options in the file
     *
     * @return array
     */
    public function readConfigFile($sConfigFile, $sLibKey = '', $sAppKey = null)
    {
        $sExt = pathinfo($sConfigFile, PATHINFO_EXTENSION);
        switch($sExt)
        {
        case 'php':
            return $this->readPhpConfigFile($sConfigFile, $sLibKey, $sAppKey);
        case 'yaml':
        case 'yml':
            return $this->readYamlConfigFile($sConfigFile, $sLibKey, $sAppKey);
        case 'json':
            return $this->readJsonConfigFile($sConfigFile, $sLibKey, $sAppKey);
        default:
            $sErrorMsg = jaxon_trans('config.errors.file.extension', array('path' => $sConfigFile));
            throw new \Jaxon\Exception\Config\File($sErrorMsg);
        }
    }
}
