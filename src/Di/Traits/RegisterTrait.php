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
        $sCallableObject = $sClassName . '_CallableObject';
        $sReflectionClass = $sClassName . '_ReflectionClass';

        // Prevent duplication
        if($this->h($sReflectionClass)) // It's important not to use the class name here.
        {
            return;
        }

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
        $this->set($sCallableObject, function($di) use($sReflectionClass, $sClassName, $aOptions) {
            $xRepository = $di->g(CallableRepository::class);
            $aProtectedMethods = $xRepository->getProtectedMethods($sClassName);
            return new CallableObject($di, $di->g(AnnotationReaderInterface::class),
                $di->g($sReflectionClass), $aOptions, $aProtectedMethods);
        });

        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, function($di) use($sReflectionClass) {
                return $this->make($di->g($sReflectionClass));
            });
        }
        // Initialize the user class instance
        $this->xLibContainer->extend($sClassName, function($xRegisteredObject) use($sCallableObject) {
            if($xRegisteredObject instanceof AbstractCallable)
            {
                $xRegisteredObject->_initCallable($this);
            }

            // Run the callbacks for class initialisation
            $this->g(CallbackManager::class)->onInit($xRegisteredObject);

            // Set attributes from the DI container.
            // The class level DI options are set when creating the object instance.
            // The method level DI options are set only when calling the method in the ajax request.
            /** @var CallableObject */
            $xCallableObject = $this->g($sCallableObject);
            $xCallableObject->setDiClassAttributes($xRegisteredObject);

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
     * @param string $sClassName    The package class name
     *
     * @return string
     */
    private function getPackageConfigKey(string $sClassName): string
    {
        return $sClassName . '_config';
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
        $sPkgConfigKey = $this->getPackageConfigKey($sClassName);
        $this->val($sPkgConfigKey, $xPkgConfig);
        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, function() use($sClassName) {
                return $this->make($sClassName);
            });
        }
        $this->xLibContainer->extend($sClassName, function($xPackage) use($sPkgConfigKey) {
            $di = $this;
            $cSetter = function() use($di, $sPkgConfigKey) {
                // Set the protected attributes of the object
                $this->_init($di->g($sPkgConfigKey), $di->g(ViewRenderer::class));
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
