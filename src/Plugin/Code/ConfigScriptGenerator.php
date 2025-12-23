<?php

/**
 * ConfigScriptGenerator.php
 *
 * Generate the config script for Jaxon.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Code;

use Jaxon\App\Config\ConfigManager;
use Jaxon\Plugin\AbstractCodeGenerator;
use Jaxon\Plugin\JsCode;
use Jaxon\Plugin\JsCodeGeneratorInterface;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Utils\Http\UriException;
use Jaxon\Utils\Template\TemplateEngine;

class ConfigScriptGenerator extends AbstractCodeGenerator implements JsCodeGeneratorInterface
{
    /**
     * The constructor
     *
     * @param ParameterReader $xParameterReader
     * @param TemplateEngine $xTemplateEngine
     * @param ConfigManager $xConfigManager
     */
    public function __construct(private ParameterReader $xParameterReader,
        private TemplateEngine $xTemplateEngine, private ConfigManager $xConfigManager)
    {}

    /**
     * Get the value of a config option
     *
     * @param string $sName The option name
     *
     * @return mixed
     */
    private function option(string $sName): mixed
    {
        return $this->xConfigManager->getOption($sName);
    }

    /**
     * @inheritDoc
     * @throws UriException
     */
    public function getJsCode(): JsCode
    {
        // It is important to call $this->xParameterReader->uri() only if necessary.
        $sUri = $this->option('core.request.uri') ?: $this->xParameterReader->uri();
        $aOptions = [
            'sResponseType'      => 'JSON',
            'sVersion'           => $this->option('core.version'),
            'sLanguage'          => $this->option('core.language'),
            'sRequestURI'        => $sUri,
            'sDefaultMode'       => $this->option('core.request.mode'),
            'sDefaultMethod'     => $this->option('core.request.method'),
            'sCsrfMetaName'      => $this->option('core.request.csrf_meta'),
            'bLoggingEnabled'    => $this->xConfigManager->loggingEnabled(),
            'bDebug'             => $this->option('core.debug.on'),
            'bVerboseDebug'      => $this->option('core.debug.verbose'),
            'sDebugOutputID'     => $this->option('core.debug.output_id'),
            'nResponseQueueSize' => $this->option('js.lib.queue_size'),
            'sStatusMessages'    => $this->option('js.lib.show_status') ? 'true' : 'false',
            'sWaitCursor'        => $this->option('js.lib.show_cursor') ? 'true' : 'false',
        ];
        $sJsCode = $this->xTemplateEngine->render('jaxon::plugins/config.js', $aOptions);

        return new JsCode(sCodeBefore: $sJsCode);
    }
}
