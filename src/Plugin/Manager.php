<?php

/**
 * Manager.php - Jaxon plugin manager
 *
 * Register Jaxon plugins, generate corresponding code, handle request
 * and redirect them to the right plugin.
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

namespace Jaxon\Plugin;

use Jaxon\Jaxon;
use Jaxon\Contracts\Dialogs\Message;
use Jaxon\Contracts\Dialogs\Question;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Request\Plugin\CallableClass;
use Jaxon\Request\Plugin\CallableDir;
use Jaxon\Request\Plugin\CallableFunction;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Response\Plugin\JQuery as JQueryPlugin;
use Jaxon\Response\Plugin\DataBag;
use Jaxon\Response\Response as JaxonResponse;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Exception\FileAccess;
use Jaxon\Utils\Config\Exception\FileContent;
use Jaxon\Utils\Config\Exception\FileExtension;
use Jaxon\Utils\Config\Exception\YamlExtension;
use Jaxon\Utils\Config\Exception\DataDepth;

use function trim;
use function is_integer;
use function is_string;
use function is_array;
use function is_subclass_of;

class Manager
{
    use \Jaxon\Features\Manager;
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Translator;

    /**
     * @var Jaxon
     */
    private $jaxon;

    /**
     * Request plugins, indexed by name
     *
     * @var array
     */
    private $aRequestPlugins = [];

    /**
     * Response plugins, indexed by name
     *
     * @var array
     */
    private $aResponsePlugins = [];

    /**
     * The code generator
     *
     * @var CodeGenerator
     */
    private $xCodeGenerator;

    /**
     * The constructor
     *
     * @param Jaxon $jaxon
     * @param CodeGenerator $xCodeGenerator
     */
    public function __construct(Jaxon $jaxon, CodeGenerator $xCodeGenerator)
    {
        $this->jaxon = $jaxon;
        $this->xCodeGenerator = $xCodeGenerator;
    }

    /**
     * Get the request plugins
     *
     * @return array
     */
    public function getRequestPlugins(): array
    {
        return $this->aRequestPlugins;
    }

    /**
     * Get a package instance
     *
     * @param string        $sClassName           The package class name
     *
     * @return Package
     */
    public function getPackage(string $sClassName): Package
    {
        return $this->jaxon->di()->get(trim($sClassName, '\\ '));
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 thru 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 thru 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param string $sClassName The plugin class
     * @param string $sPluginName The plugin name
     * @param integer $nPriority The plugin priority, used to order the plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugin(string $sClassName, string $sPluginName, int $nPriority = 1000)
    {
        $bIsUsed = false;
        if(is_subclass_of($sClassName, Request::class))
        {
            $this->aRequestPlugins[$sPluginName] = $sClassName;
            $this->xCodeGenerator->addGenerator($sClassName, $nPriority);
            $bIsUsed = true;
        }
        elseif(is_subclass_of($sClassName, Response::class))
        {
            $this->aResponsePlugins[$sPluginName] = $sClassName;
            $this->xCodeGenerator->addGenerator($sClassName, $nPriority);
            $bIsUsed = true;
        }

        // This plugin implements the Message interface
        if(is_subclass_of($sClassName, Message::class))
        {
            $this->jaxon->dialog()->setMessage($sClassName);
            $bIsUsed = true;
        }
        // This plugin implements the Question interface
        if(is_subclass_of($sClassName, Question::class))
        {
            $this->jaxon->dialog()->setQuestion($sClassName);
            $bIsUsed = true;
        }

        if(!$bIsUsed)
        {
            $sMessage = $this->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Register a package
     *
     * @param string $sClassName The package class name
     *
     * @return Config
     * @throws SetupException
     */
    private function readPackageConfig(string $sClassName): Config
    {
        $sConfigFile = $sClassName::getConfigFile();
        try
        {
            $di = $this->jaxon->di();
            $aPackageConfig = $di->getConfigReader()->read($sConfigFile);
            // Add the package name to the config
            $aPackageConfig['package'] = $sClassName;
            return $di->newConfig($aPackageConfig);
        }
        catch(YamlExtension $e)
        {
            $sMessage = $this->trans('errors.yaml.install');
            throw new SetupException($sMessage);
        }
        catch(FileExtension $e)
        {
            $sMessage = $this->trans('errors.file.extension', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileAccess $e)
        {
            $sMessage = $this->trans('errors.file.access', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileContent $e)
        {
            $sMessage = $this->trans('errors.file.content', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->trans('errors.data.depth', ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Register a package
     *
     * @param string $sClassName The package class name
     * @param array $aAppOptions The package options defined in the app section of the config file
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aAppOptions)
    {
        $sClassName = trim($sClassName, '\\ ');
        $di = $this->jaxon->di();
        $xAppConfig = $di->registerPackage($sClassName, $aAppOptions);

        // Read the package config.
        $xPackageConfig = $this->readPackageConfig($sClassName);

        // Register the declarations in the package config.
        $this->_registerFromConfig($xPackageConfig);

        // Register the view namespaces
        $di->getViewManager()->addNamespaces($xPackageConfig, $xAppConfig);

        // Register the package as a code generator.
        $this->xCodeGenerator->addGenerator($sClassName, 500);
    }

    /**
     * Register a function or callable class
     *
     * Call the request plugin with the $sType defined as name.
     *
     * @param string $sType The type of request handler being registered
     * @param string $sCallable The callable entity being registered
     * @param array|string $aOptions The associated options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallable(string $sType, string $sCallable, $aOptions = [])
    {
        if(!isset($this->aRequestPlugins[$sType]))
        {
            throw new SetupException($this->trans('errors.register.plugin', ['name' => $sType]));
        }

        $xPlugin = $this->jaxon->di()->g($this->aRequestPlugins[$sType]);
        $xPlugin->register($sType, $sCallable, $aOptions);
    }

    /**
     * Register callables from a section of the config
     *
     * @param Config $xAppConfig The config options
     * @param string $sSection The config section name
     * @param string $sCallableType The type of callable to register
     *
     * @return void
     * @throws SetupException
     */
    private function registerCallablesFromConfig(Config $xAppConfig, string $sSection, string $sCallableType)
    {
        $aConfig = $xAppConfig->getOption($sSection, []);
        foreach($aConfig as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a function without options
                $this->registerCallable($sCallableType, $xValue);
            }
            elseif(is_string($xKey) && (is_array($xValue) || is_string($xValue)))
            {
                // Register a function with options
                $this->registerCallable($sCallableType, $xKey, $xValue);
            }
            else
            {
                continue;
                // Todo: throw an exception
            }
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xAppConfig The config options
     *
     * @return void
     * @throws SetupException
     */
    private function _registerFromConfig(Config $xAppConfig)
    {
        // Register functions
        $this->registerCallablesFromConfig($xAppConfig, 'functions', Jaxon::CALLABLE_FUNCTION);

        // Register classes
        $this->registerCallablesFromConfig($xAppConfig, 'classes', Jaxon::CALLABLE_CLASS);

        // Register directories
        $this->registerCallablesFromConfig($xAppConfig, 'directories', Jaxon::CALLABLE_DIR);

        // Register classes in DI container
        $di = $this->jaxon->di();
        $aContainerConfig = $xAppConfig->getOption('container', []);
        foreach($aContainerConfig as $sClassName => $xClosure)
        {
            $di->set($sClassName, $xClosure);
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xAppConfig The config options
     *
     * @return void
     * @throws SetupException
     */
    public function registerFromConfig(Config $xAppConfig)
    {
        $this->_registerFromConfig($xAppConfig);

        // Register packages
        $aPackageConfig = $xAppConfig->getOption('packages', []);
        foreach($aPackageConfig as $sClassName => $aOptions)
        {
            $this->registerPackage($sClassName, $aOptions);
        }
    }


    /**
     * Find the specified response plugin by name and return a reference to it if one exists
     *
     * @param string $sName The name of the plugin
     * @param JaxonResponse|null $xResponse The response to attach the plugin to
     *
     * @return Response
     */
    public function getResponsePlugin(string $sName, ?JaxonResponse $xResponse = null): ?Response
    {
        if(!isset($this->aResponsePlugins[$sName]))
        {
            return null;
        }
        $xPlugin = $this->jaxon->di()->g($this->aResponsePlugins[$sName]);
        if(($xResponse))
        {
            $xPlugin->setResponse($xResponse);
        }
        return $xPlugin;
    }

    /**
     * Find the specified request plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return Request
     */
    public function getRequestPlugin(string $sName): ?Request
    {
        if(!isset($this->aRequestPlugins[$sName]))
        {
            return null;
        }
        return $this->jaxon->di()->g($this->aRequestPlugins[$sName]);
    }

    /**
     * Register the Jaxon request plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerRequestPlugins()
    {
        $this->registerPlugin(CallableClass::class, Jaxon::CALLABLE_CLASS, 101);
        $this->registerPlugin(CallableFunction::class, Jaxon::CALLABLE_FUNCTION, 102);
        $this->registerPlugin(FileUpload::class, Jaxon::FILE_UPLOAD, 103);
        $this->registerPlugin(CallableDir::class, Jaxon::CALLABLE_DIR, 104);
    }

    /**
     * Register the Jaxon response plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerResponsePlugins()
    {
        // Register an instance of the JQuery plugin
        $this->registerPlugin(JQueryPlugin::class, 'jquery', 700);
        // Register an instance of the DataBag plugin
        $this->registerPlugin(DataBag::class, 'bags', 700);
    }
}
