<?php

namespace Jaxon\App\View;

use Jaxon\Di\Container;
use Jaxon\Utils\Config\Config;

use Closure;

use function array_filter;
use function array_merge;
use function is_array;
use function rtrim;
use function strrpos;
use function substr;

class ViewRenderer
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * The view namespaces
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * The view data store
     *
     * @var Store
     */
    protected $xStore = null;

    /**
     * The default namespace
     *
     * @var string
     */
    protected $sDefaultNamespace = 'jaxon';

    /**
     * The view global data
     *
     * @var array
     */
    protected $aViewData = [];

    /**
     * The class constructor
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Add a view namespace, and set the corresponding renderer.
     *
     * @param string $sNamespace    The namespace name
     * @param string $sDirectory    The namespace directory
     * @param string $sExtension    The extension to append to template names
     * @param string $sRenderer    The corresponding renderer name
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension, string $sRenderer)
    {
        $aNamespace = [
            'directory' => $sDirectory,
            'extension' => $sExtension,
            'renderer' => $sRenderer,
        ];
        $this->aNamespaces[$sNamespace] = $aNamespace;
    }

    /**
     * Set the view namespaces.
     *
     * @param Config $xAppConfig    The config options provided in the library
     * @param Config|null $xUserConfig    The config options provided in the app section of the global config file.
     *
     * @return void
     */
    public function addNamespaces(Config $xAppConfig, ?Config $xUserConfig = null)
    {
        if(empty($aNamespaces = $xAppConfig->getOptionNames('views')))
        {
            return;
        }
        $sPackage = $xAppConfig->getOption('package', '');
        foreach($aNamespaces as $sNamespace => $sOption)
        {
            // Save the namespace
            $aNamespace = $xAppConfig->getOption($sOption);
            $aNamespace['package'] = $sPackage;
            if(!isset($aNamespace['renderer']))
            {
                $aNamespace['renderer'] = 'jaxon'; // 'jaxon' is the default renderer.
            }

            // If the lib config has defined a template option, then its value must be
            // read from the app config.
            if($xUserConfig !== null && isset($aNamespace['template']) && is_array($aNamespace['template']))
            {
                $sTemplateOption = $xAppConfig->getOption($sOption . '.template.option');
                $sTemplateDefault = $xAppConfig->getOption($sOption . '.template.default');
                $sTemplate = $xUserConfig->getOption($sTemplateOption, $sTemplateDefault);
                $aNamespace['directory'] = rtrim($aNamespace['directory'], '/') . '/' . $sTemplate;
            }

            $this->aNamespaces[$sNamespace] = $aNamespace;
        }
    }

    /**
     * Get the view renderer
     *
     * @param string $sId    The unique identifier of the view renderer
     *
     * @return ViewInterface
     */
    public function getRenderer(string $sId): ViewInterface
    {
        // Return the view renderer with the given id
        return $this->di->g('jaxon.app.view.' . $sId);
    }

    /**
     * Add a view renderer with an id
     *
     * @param string $sId    The unique identifier of the view renderer
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return void
     */
    public function addRenderer(string $sId, Closure $xClosure)
    {
        // Return the initialized view renderer
        $this->di->set('jaxon.app.view.' . $sId, function($di) use($sId, $xClosure) {
            // Get the defined renderer
            $xRenderer = $xClosure($di);
            // Init the renderer with the template namespaces
            $aNamespaces = array_filter($this->aNamespaces, function($aNamespace) use($sId) {
                return $aNamespace['renderer'] === $sId;
            });
            foreach($aNamespaces as $sNamespace => $aNamespace)
            {
                $xRenderer->addNamespace($sNamespace, $aNamespace['directory'], $aNamespace['extension']);
            }
            return $xRenderer;
        });
    }

    /**
     * Add a view renderer with an id
     *
     * @param string $sId    The unique identifier of the view renderer
     * @param string $sExtension    The extension to append to template names
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return void
     */
    public function setDefaultRenderer(string $sId, string $sExtension, Closure $xClosure)
    {
        $this->setDefaultNamespace($sId);
        $this->addNamespace($sId, '', $sExtension, $sId);
        $this->addRenderer($sId, $xClosure);
    }

    /**
     * Get the view renderer for a given namespace
     *
     * @param string $sNamespace    The namespace name
     *
     * @return ViewInterface|null
     */
    public function getNamespaceRenderer(string $sNamespace): ?ViewInterface
    {
        if(!isset($this->aNamespaces[$sNamespace]))
        {
            return null;
        }
        // Return the view renderer with the configured id
        return $this->getRenderer($this->aNamespaces[$sNamespace]['renderer']);
    }

    /**
     * Set the default namespace
     *
     * @param string $sDefaultNamespace
     */
    public function setDefaultNamespace(string $sDefaultNamespace): void
    {
        $this->sDefaultNamespace = $sDefaultNamespace;
    }

    /**
     * Get the current store or create a new store
     *
     * @return Store
     */
    protected function store(): Store
    {
        if(!$this->xStore)
        {
            $this->xStore = new Store();
        }
        return $this->xStore;
    }

    /**
     * Make a piece of data available for the rendered view
     *
     * @param string $sName    The data name
     * @param mixed $xValue    The data value
     *
     * @return ViewRenderer
     */
    public function set(string $sName, $xValue): ViewRenderer
    {
        $this->store()->with($sName, $xValue);
        return $this;
    }

    /**
     * Make a piece of data available for all views
     *
     * @param string $sName    The data name
     * @param mixed $xValue    The data value
     *
     * @return ViewRenderer
     */
    public function share(string $sName, $xValue): ViewRenderer
    {
        $this->aViewData[$sName] = $xValue;
        return $this;
    }

    /**
     * Make an array of data available for all views
     *
     * @param array $aValues    The data values
     *
     * @return ViewRenderer
     */
    public function shareValues(array $aValues): ViewRenderer
    {
        foreach($aValues as $sName => $xValue)
        {
            $this->share($sName, $xValue);
        }
        return $this;
    }

    /**
     * Render a view using a store
     *
     * The store returned by this function will later be used with the make() method to render the view.
     *
     * @param string $sViewName    The view name
     * @param array $aViewData    The view data
     *
     * @return null|Store   A store populated with the view data
     */
    public function render(string $sViewName, array $aViewData = []): ?Store
    {
        $xStore = $this->store();
        // Get the default view namespace
        $sNamespace = $this->sDefaultNamespace;
        // Get the namespace from the view name
        $nSeparatorPosition = strrpos($sViewName, '::');
        if($nSeparatorPosition !== false)
        {
            $sNamespace = substr($sViewName, 0, $nSeparatorPosition);
        }
        $xRenderer = $this->getNamespaceRenderer($sNamespace);
        if(!$xRenderer)
        {
            // Cannot render a view if there's no renderer corresponding to the namespace.
            return null;
        }
        $xStore->setData(array_merge($this->aViewData, $aViewData))->setView($xRenderer, $sNamespace, $sViewName);
        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $xStore;
    }
}
