<?php

/**
 * PluginManager.php - Jaxon plugin registry
 *
 * Register Jaxon plugins and callables.
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

namespace Jaxon\Plugin\Manager;

use Jaxon\Jaxon;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\CallableRegistryInterface;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\CodeGeneratorInterface;
use Jaxon\Plugin\CssCodeGeneratorInterface;
use Jaxon\Plugin\JsCodeGeneratorInterface;
use Jaxon\Plugin\PluginInterface;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableDirPlugin;
use Jaxon\Plugin\Request\CallableFunction\CallableFunctionPlugin;
use Jaxon\Plugin\RequestHandlerInterface;
use Jaxon\Plugin\Response\Databag\DatabagPlugin;
use Jaxon\Plugin\Response\Dialog\DialogPlugin;
use Jaxon\Plugin\Response\Script\ScriptPlugin;
use Jaxon\Plugin\Response\Psr\PsrPlugin;
use Jaxon\Plugin\ResponsePluginInterface;
use Jaxon\Request\Handler\ParameterReader;

use function class_implements;
use function in_array;

class PluginManager
{
    /**
     * Request plugins, indexed by name
     *
     * @var array<string>
     */
    private $aRegistryPlugins = [];

    /**
     * Request handlers, indexed by name
     *
     * @var array<string>
     */
    private $aRequestHandlers = [];

    /**
     * Response plugins, indexed by name
     *
     * @var array<string>
     */
    private $aResponsePlugins = [];

    /**
     * The constructor
     *
     * @param Container $di
     * @param CodeGenerator $xCodeGenerator
     * @param Translator $xTranslator
     */
    public function __construct(private Container $di,
        private CodeGenerator $xCodeGenerator, private Translator $xTranslator)
    {}

    /**
     * Get the request plugins
     *
     * @return array<class-string>
     */
    public function getRequestHandlers(): array
    {
        return $this->aRequestHandlers;
    }

    /**
     * Register a plugin
     *
     * @param class-string $sClassName    The plugin class
     * @param string $sPluginName    The plugin name
     * @param array $aInterfaces    The plugin interfaces
     *
     * @return int
     * @throws SetupException
     */
    private function _registerPlugin(string $sClassName, string $sPluginName, array $aInterfaces): int
    {
        // Any plugin must implement the PluginInterface interface.
        if(!in_array(PluginInterface::class, $aInterfaces))
        {
            $sMessage = $this->xTranslator->trans('errors.register.invalid', [
                'name' => $sClassName,
            ]);
            throw new SetupException($sMessage);
        }

        // Response plugin.
        if(in_array(ResponsePluginInterface::class, $aInterfaces))
        {
            $this->aResponsePlugins[$sPluginName] = $sClassName;
            return 1;
        }

        // Request plugin.
        $nCount = 0;
        if(in_array(CallableRegistryInterface::class, $aInterfaces))
        {
            $this->aRegistryPlugins[$sPluginName] = $sClassName;
            $nCount++;
        }
        if(in_array(RequestHandlerInterface::class, $aInterfaces))
        {
            $this->aRequestHandlers[$sPluginName] = $sClassName;
            $nCount++;
        }
        return $nCount;
    }

    /**
     * @param string $sClassName
     * @param int $nPriority
     * @param array $aInterfaces
     *
     * @return int
     */
    private function _registerCodeGenerator(string $sClassName, int $nPriority, array $aInterfaces): int
    {
        // Any plugin can implement the one of the 3 code generator interfaces.
        $nCount = 0;
        if(in_array(CssCodeGeneratorInterface::class, $aInterfaces))
        {
            $this->xCodeGenerator->addCssCodeGenerator($sClassName, $nPriority);
            $nCount++;
        }
        if(in_array(JsCodeGeneratorInterface::class, $aInterfaces))
        {
            $this->xCodeGenerator->addJsCodeGenerator($sClassName, $nPriority);
            $nCount++;
        }
        if(in_array(CodeGeneratorInterface::class, $aInterfaces))
        {
            $this->xCodeGenerator->addCodeGenerator($sClassName, $nPriority);
            $nCount++;
        }
        return $nCount;
    }

    /**
     * @param string $sClassName
     * @param int $nPriority
     *
     * @return void
     */
    public function registerCodeGenerator(string $sClassName, int $nPriority): void
    {
        $aInterfaces = class_implements($sClassName);
        $this->_registerCodeGenerator($sClassName, $nPriority, $aInterfaces);
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 to 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 to 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 to 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param class-string $sClassName    The plugin class
     * @param string $sPluginName    The plugin name
     * @param integer $nPriority    The plugin priority, used to order the plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugin(string $sClassName, string $sPluginName, int $nPriority = 1000): void
    {
        $aInterfaces = class_implements($sClassName);
        $nCount = $this->_registerPlugin($sClassName, $sPluginName, $aInterfaces);
        $nCount += $this->_registerCodeGenerator($sClassName, $nPriority, $aInterfaces);

        // The class is not a valid plugin.
        if($nCount === 0)
        {
            $sMessage = $this->xTranslator->trans('errors.register.invalid', [
                'name' => $sClassName,
            ]);
            throw new SetupException($sMessage);
        }

        // Register the plugin in the DI container, if necessary
        if(!$this->di->has($sClassName))
        {
            $this->di->auto($sClassName);
        }
    }

    /**
     * Find the specified response plugin by name or class name
     *
     * @template R of ResponsePluginInterface
     * @param string|class-string<R> $sName    The name or class of the plugin
     *
     * @return ($sName is class-string ? R|null : ResponsePluginInterface|null)
     */
    public function getResponsePlugin(string $sName): ?ResponsePluginInterface
    {
        return $this->di->h($sName) ? $this->di->g($sName) :
            (!isset($this->aResponsePlugins[$sName]) ? null :
            $this->di->g($this->aResponsePlugins[$sName]));
    }

    /**
     * Register a callable function or class
     *
     * Call the request plugin with the $sType defined as name.
     *
     * @param string $sType    The type of request handler being registered
     * @param string $sCallable    The callable entity being registered
     * @param array|string $xOptions    The associated options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallable(string $sType, string $sCallable, $xOptions = []): void
    {
        if(isset($this->aRegistryPlugins[$sType]) &&
            ($xPlugin = $this->di->g($this->aRegistryPlugins[$sType])))
        {
            $xPlugin->register($sType, $sCallable, $xPlugin->checkOptions($sCallable, $xOptions));
            return;
        }
        throw new SetupException($this->xTranslator->trans('errors.register.plugin',
            ['name' => $sType, 'callable' => $sCallable]));
    }

    /**
     * Register the Jaxon request plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugins(): void
    {
        // Request plugins
        $this->registerPlugin(CallableClassPlugin::class, Jaxon::CALLABLE_CLASS, 101);
        $this->registerPlugin(CallableFunctionPlugin::class, Jaxon::CALLABLE_FUNCTION, 102);
        $this->registerPlugin(CallableDirPlugin::class, Jaxon::CALLABLE_DIR, 103);

        // Response plugins
        $this->registerPlugin(ScriptPlugin::class, ScriptPlugin::NAME, 700);
        $this->registerPlugin(DatabagPlugin::class, DatabagPlugin::NAME, 700);
        $this->registerPlugin(DialogPlugin::class, DialogPlugin::NAME, 750);
        $this->registerPlugin(PsrPlugin::class, PsrPlugin::NAME, 850);
    }

    /**
     * Get the parameter reader
     *
     * @return ParameterReader
     */
    public function getParameterReader(): ParameterReader
    {
        return $this->di->g(ParameterReader::class);
    }
}
