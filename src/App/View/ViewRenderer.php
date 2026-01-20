<?php

namespace Jaxon\App\View;

use Jaxon\Config\Config;
use Jaxon\Di\Container;
use Closure;

use function array_filter;
use function array_merge;
use function strrpos;
use function substr;

class ViewRenderer
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * The view data store
     *
     * @var Store|null
     */
    protected $xStore = null;

    /**
     * The view data store
     *
     * @var Store
     */
    protected $xEmptyStore = null;

    /**
     * The view namespaces
     *
     * @var array
     */
    protected $aNamespaces = [];

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
        $this->xEmptyStore = new Store();
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
    public function addNamespace(string $sNamespace, string $sDirectory,
        string $sExtension, string $sRenderer): void
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
     *
     * @return void
     */
    public function addNamespaces(Config $xAppConfig): void
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
        return $this->di->g("jaxon.app.view.$sId");
    }

    /**
     * Add a view renderer with an id
     *
     * @param string $sId    The unique identifier of the view renderer
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return void
     */
    public function addRenderer(string $sId, Closure $xClosure): void
    {
        // Return the initialized view renderer
        $this->di->set("jaxon.app.view.$sId", function($di) use($sId, $xClosure) {
            // Get the defined renderer
            $xRenderer = $xClosure($di);
            // Init the renderer with the template namespaces
            $aNamespaces = array_filter($this->aNamespaces,
                fn($aOptions) => $aOptions['renderer'] === $sId);
            foreach($aNamespaces as $sName => $aOptions)
            {
                $xRenderer->addNamespace($sName, $aOptions['directory'], $aOptions['extension']);
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
    public function setDefaultRenderer(string $sId, string $sExtension, Closure $xClosure): void
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
     * @return Store   A store populated with the view data
     */
    public function render(string $sViewName, array $aViewData = []): Store
    {
        $xStore = $this->store();
        // Get the default view namespace
        $sNamespace = $this->sDefaultNamespace;
        // Get the namespace from the view name
        $nSeparatorPosition = strrpos($sViewName, '::');
        if($nSeparatorPosition !== false)
        {
            $sNamespace = substr($sViewName, 0, $nSeparatorPosition);
            $sViewName = substr($sViewName, $nSeparatorPosition + 2);
        }

        $xRenderer = $this->getNamespaceRenderer($sNamespace);
        if(!$xRenderer)
        {
            // Cannot render a view if there's no renderer corresponding to the namespace.
            return $this->xEmptyStore;
        }

        $xStore->setData(array_merge($this->aViewData, $aViewData))
            ->setView($xRenderer, $sNamespace, $sViewName);

        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $xStore;
    }
}
