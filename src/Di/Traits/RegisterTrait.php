<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\AbstractCallable;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Plugin\Request\CallableClass\CallableRepository;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Utils\Config\Config;
use ReflectionClass;
use ReflectionException;

use function call_user_func;

trait RegisterTrait
{
    /**
     * @param string $sClassName The callable class name
     *
     * @return string
     */
    private function getCallableObjectKey(string $sClassName): string
    {
        return $sClassName . '_CallableObject';
    }

    /**
     * @param string $sClassName The callable class name
     *
     * @return string
     */
    private function getReflectionClassKey(string $sClassName): string
    {
        return $sClassName . '_ReflectionClass';
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
        $sCallableObject = $this->getCallableObjectKey($sClassName);
        // Prevent duplication. It's important not to use the class name here.
        if($this->h($sCallableObject))
        {
            return;
        }

        // Register the reflection class
        try
        {
            // Make sure the registered class exists
            isset($aOptions['include']) && require_once($aOptions['include']);
            $this->val($this->getReflectionClassKey($sClassName), new ReflectionClass($sClassName));
        }
        catch(ReflectionException $e)
        {
            $xTranslator = $this->g(Translator::class);
            $sMessage = $xTranslator->trans('errors.class.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the callable object
        $this->set($sCallableObject, function($di) use($sClassName, $aOptions) {
            $xReflectionClass = $di->g($this->getReflectionClassKey($sClassName));
            $xRepository = $di->g(CallableRepository::class);
            $aProtectedMethods = $xRepository->getProtectedMethods($sClassName);

            return new CallableObject($di, $di->g(AnnotationReaderInterface::class),
                $xReflectionClass, $aOptions, $aProtectedMethods);
        });

        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, function($di) use($sClassName) {
                return $this->make($di->g($this->getReflectionClassKey($sClassName)));
            });
        }
        // Initialize the user class instance
        $this->xLibContainer->extend($sClassName, function($xClassInstance) use($sClassName) {
            if($xClassInstance instanceof AbstractCallable)
            {
                $xClassInstance->_initCallable($this);
            }

            // Run the callbacks for class initialisation
            $this->g(CallbackManager::class)->onInit($xClassInstance);

            // Set attributes from the DI container.
            // The class level DI options are set when creating the object instance.
            // The method level DI options are set only when calling the method in the ajax request.
            /** @var CallableObject */
            $xCallableObject = $this->g($this->getCallableObjectKey($sClassName));
            $xCallableObject->setDiClassAttributes($xClassInstance);

            return $xClassInstance;
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
     * @param string $sClassName    The package class name
     *
     * @return string
     */
    private function getPackageConfigKey(string $sClassName): string
    {
        return $sClassName . '_PackageConfig';
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
        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, function() use($sClassName) {
                return $this->make($sClassName);
            });
        }

        // Save the package config in the container.
        $this->val($this->getPackageConfigKey($sClassName), $xPkgConfig);

        // Initialize the package instance.
        $this->xLibContainer->extend($sClassName, function($xPackage) use($sClassName) {
            $xPkgConfig = $this->getPackageConfig($sClassName);
            $xViewRenderer = $this->g(ViewRenderer::class);
            $cSetter = function() use($xPkgConfig, $xViewRenderer) {
                // Set the protected attributes of the Package instance.
                $this->xPkgConfig = $xPkgConfig;
                $this->xRenderer = $xViewRenderer;
                $this->init();
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xPackage, $xPackage));
            return $xPackage;
        });
    }

    /**
     * Get the config of a package
     *
     * @param string $sClassName    The package class name
     *
     * @return Config
     */
    public function getPackageConfig(string $sClassName): Config
    {
        return $this->g($this->getPackageConfigKey($sClassName));
    }
}
