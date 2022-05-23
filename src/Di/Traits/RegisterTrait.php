<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\CallableClass;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Request\Call\Paginator;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Utils\Config\Config;

use ReflectionClass;
use ReflectionException;

use function array_merge;
use function call_user_func;
use function explode;
use function substr;

trait RegisterTrait
{
    /**
     * @param array $aConfigOptions
     * @param array $aAnnotationOptions
     *
     * @return array
     */
    private function getCallableObjectOptions(array $aConfigOptions, array $aAnnotationOptions): array
    {
        $aOptions = [];
        foreach($aConfigOptions as $sNames => $aFunctionOptions)
        {
            $aFunctionNames = explode(',', $sNames); // Names are in comma-separated list.
            foreach($aFunctionNames as $sFunctionName)
            {
                $aOptions[$sFunctionName] = array_merge($aOptions[$sFunctionName] ?? [], $aFunctionOptions);
            }
        }
        foreach($aAnnotationOptions as $sFunctionName => $aFunctionOptions)
        {
            $aOptions[$sFunctionName] = array_merge($aOptions[$sFunctionName] ?? [], $aFunctionOptions);
        }
        return $aOptions;
    }

    /**
     * @param string $sClassName
     * @param CallableObject $xCallableObject
     * @param array $aOptions
     *
     * @return void
     */
    private function setCallableObjectOptions(string $sClassName, CallableObject $xCallableObject, array $aOptions)
    {
        $aProtectedMethods = $this->getCallableRepository()->getProtectedMethods($sClassName);
        // Annotations options
        $xAnnotationReader = $this->g(AnnotationReaderInterface::class);
        $aMethods = $xCallableObject->getPublicMethods($aProtectedMethods);
        $aProperties = $xCallableObject->getProperties();
        [$bExcluded, $aAnnotationOptions, $aAnnotationProtected] = $xAnnotationReader->getAttributes($sClassName, $aMethods, $aProperties);
        if($bExcluded)
        {
            $xCallableObject->configure('excluded', true);
            return;
        }

        $xCallableObject->configure('separator', $aOptions['separator']);
        $xCallableObject->configure('protected', array_merge($aOptions['protected'], $aAnnotationProtected));

        // Functions options
        $aCallableOptions = [];
        $aOptions = $this->getCallableObjectOptions($aOptions['functions'], $aAnnotationOptions);
        foreach($aOptions as $sFunctionName => $aFunctionOptions)
        {
            foreach($aFunctionOptions as $sOptionName => $xOptionValue)
            {
                if(substr($sOptionName, 0, 2) !== '__')
                {
                    // Options for javascript code.
                    $aCallableOptions[$sFunctionName][$sOptionName] = $xOptionValue;
                    continue;
                }
                // Options for PHP classes. They start with "__".
                $xCallableObject->configure($sOptionName, [$sFunctionName => $xOptionValue]);
            }
        }
        $xCallableObject->setOptions($aCallableOptions);
    }

    /**
     * Register a callable class
     *
     * @param string $sClassName The callable class name
     * @param array $aOptions The callable object options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallableClass(string $sClassName, array $aOptions)
    {
        $sRequestFactory = $sClassName . '_RequestFactory';
        $sCallableObject = $sClassName . '_CallableObject';
        $sReflectionClass = $sClassName . '_ReflectionClass';

        // Make sure the registered class exists
        if(isset($aOptions['include']))
        {
            require_once($aOptions['include']);
        }
        // Register the reflection class
        try
        {
            $this->val($sReflectionClass, new ReflectionClass($sClassName));
        }
        catch(ReflectionException $e)
        {
            $xTranslator = $this->g(Translator::class);
            $sMessage = $xTranslator->trans('errors.class.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the callable object
        $this->set($sCallableObject, function($c) use($sClassName, $sReflectionClass, $aOptions) {
            $xCallableObject = new CallableObject($this, $c->g($sReflectionClass));
            $this->setCallableObjectOptions($sClassName, $xCallableObject, $aOptions);
            return $xCallableObject;
        });

        // Register the request factory
        $this->set($sRequestFactory, function($c) use($sCallableObject) {
            $xConfigManager = $c->g(ConfigManager::class);
            $xCallable = $c->g($sCallableObject);
            $sJsClass = $xConfigManager->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
            return new RequestFactory($sJsClass, $c->g(DialogLibraryManager::class), $c->g(Paginator::class));
        });

        // Register the user class
        $this->set($sClassName, function($c) use($sClassName, $sReflectionClass) {
            $xRegisteredObject = $this->make($c->g($sReflectionClass));
            // Initialize the object
            if($xRegisteredObject instanceof CallableClass)
            {
                // Set the protected attributes of the object
                $cSetter = function($c, $sClassName) {
                    $this->xCallableClassHelper = new CallableClassHelper($c, $sClassName);
                    $this->response = $c->getResponse();
                };
                // Can now access protected attributes
                call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject), $c, $sClassName);
            }

            // Run the callback for class initialisation
            $aCallbacks = $c->g(CallbackManager::class)->getInitCallbacks();
            foreach($aCallbacks as $xCallback)
            {
                call_user_func($xCallback, $xRegisteredObject);
            }
            return $xRegisteredObject;
        });
    }

    /**
     * Get the callable object for a given class
     *
     * @param string $sClassName
     *
     * @return CallableObject
     */
    public function getCallableObject(string $sClassName): CallableObject
    {
        return $this->g($sClassName . '_CallableObject');
    }

    /**
     * Get the request factory for a given class
     *
     * @param string $sClassName
     *
     * @return RequestFactory
     */
    public function getRequestFactory(string $sClassName): RequestFactory
    {
        return $this->g($sClassName . '_RequestFactory');
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class name
     * @param Config $xPkgConfig    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, Config $xPkgConfig)
    {
        $this->val($sClassName . '_config', $xPkgConfig);
        $this->set($sClassName, function($c) use($sClassName) {
            $xPackage = $this->make($sClassName);
            // Set the protected attributes of the object
            $cSetter = function($c, $sClassName) {
                $this->xPkgConfig = $c->g($sClassName . '_config');
                $this->xFactory = $c->g(Factory::class);
                $this->xRenderer = $c->g(ViewRenderer::class);
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xPackage, $xPackage), $c, $sClassName);
            return $xPackage;
        });
    }

    /**
     * Get a package config
     *
     * @param string $sClassName    The package class name
     *
     * @return Config
     */
    public function getPackageConfig(string $sClassName): Config
    {
        return $this->g($sClassName . '_config');
    }
}
