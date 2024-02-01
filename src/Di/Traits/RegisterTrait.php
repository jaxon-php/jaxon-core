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
use Jaxon\Plugin\Request\CallableClass\CallableRepository;
use Jaxon\Request\Call\Paginator;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Target;
use Jaxon\Utils\Config\Config;
use ReflectionClass;
use ReflectionException;

use function call_user_func;

trait RegisterTrait
{
    /**
     * @param mixed $xRegisteredObject
     * @param array $aDiOptions
     *
     * @return void
     */
    private function setDiAttributes($xRegisteredObject, array $aDiOptions)
    {
        foreach($aDiOptions as $sName => $sClass)
        {
            // Set the protected attributes of the object
            $cSetter = function($xInjectedObject) use($sName) {
                // Warning: dynamic properties will be deprecated in PHP8.2.
                $this->$sName = $xInjectedObject;
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject), $this->get($sClass));
        }
    }

    /**
     * Get a callable object when one of its method needs to be called
     *
     * @param CallableObject $xCallableObject
     * @param Target $xTarget
     *
     * @return mixed
     */
    public function getRegisteredObject(CallableObject $xCallableObject, Target $xTarget)
    {
        // Set attributes from the DI container.
        // The class level DI options were set when creating the object instance.
        // We now need to set the method level DI options.
        $aDiOptions = $xCallableObject->getMethodDiOptions($xTarget->getMethodName());
        $xRegisteredObject = $this->g($xCallableObject->getClassName());
        $this->setDiAttributes($xRegisteredObject, $aDiOptions);

        // Set the Jaxon request target in the helper
        if($xRegisteredObject instanceof CallableClass)
        {
            // Set the protected attributes of the object
            $cSetter = function() use($xTarget) {
                $this->xCallableClassHelper->xTarget = $xTarget;
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject));
        }
        return $xRegisteredObject;
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

        // Register the request factory
        $this->set($sRequestFactory, function($di) use($sCallableObject) {
            $xConfigManager = $di->g(ConfigManager::class);
            $xCallable = $di->g($sCallableObject);
            $sJsClass = $xConfigManager->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
            return new RequestFactory($sJsClass, $di->g(DialogLibraryManager::class), $di->g(Paginator::class));
        });

        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, function($di) use($sReflectionClass) {
                return $this->make($di->g($sReflectionClass));
            });
        }
        // Initialize the user class instance
        $this->xLibContainer->extend($sClassName, function($xRegisteredObject)
            use($sCallableObject, $sClassName) {
            if($xRegisteredObject instanceof CallableClass)
            {
                $cSetter = function($di) use($sClassName) {
                    // Set the protected attributes of the object
                    $this->xCallableClassHelper = new CallableClassHelper($di, $sClassName);
                    $this->response = $di->getResponse();
                };
                // Can now access protected attributes
                call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject), $this);
            }

            // Run the callbacks for class initialisation
            $this->g(CallbackManager::class)->onInit($xRegisteredObject);

            // Set attributes from the DI container.
            // The class level DI options are set when creating the object instance.
            // The method level DI options are set only when calling the method in the ajax request.
            /** @var CallableObject */
            $xCallableObject = $this->g($sCallableObject);
            $this->setDiAttributes($xRegisteredObject, $xCallableObject->getClassDiOptions());

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
                $this->_init($di->g($sPkgConfigKey), $di->g(Factory::class), $di->g(ViewRenderer::class));
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
