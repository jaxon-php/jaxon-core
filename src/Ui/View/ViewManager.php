<?php

namespace Jaxon\Ui\View;

use Jaxon\Container\Container;
use Jaxon\Utils\Config\Config;

use Closure;

use function array_filter;
use function is_array;
use function rtrim;

class ViewManager
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * The default namespace
     *
     * @var string
     */
    protected $sDefaultNamespace = '';

    /**
     * The view namespaces
     *
     * @var array
     */
    protected $aNamespaces = [];

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
     * Get the default namespace
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        return $this->sDefaultNamespace;
    }

    /**
     * Get the view namespaces
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->aNamespaces;
    }

    /**
     * Find a view namespace by its name.
     *
     * @param string $sNamespace    The namespace name
     *
     * @return array|null
     */
    public function getNamespace(string $sNamespace): ?array
    {
        return $this->aNamespaces[$sNamespace] ?? null;
    }

    /**
     * Add a view namespace, and set the corresponding renderer.
     *
     * @param string $sNamespace    The corresponding renderer name
     * @param array $aNamespace    The namespace options
     *
     * @return void
     */
    private function _addNamespace(string $sNamespace, array $aNamespace)
    {
        $this->aNamespaces[$sNamespace] = $aNamespace;
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
        $this->_addNamespace($sNamespace, $aNamespace);
    }

    /**
     * Set the view namespaces.
     *
     * @param Config $xLibConfig    The config options provided in the library
     * @param Config|null $xPkgConfig    The config options provided in the app section of the global config file.
     *
     * @return void
     */
    public function addNamespaces(Config $xLibConfig, ?Config $xPkgConfig = null)
    {
        $this->sDefaultNamespace = $xLibConfig->getOption('options.views.default', '');

        $sPackage = $xLibConfig->getOption('package', '');

        if(is_array($aNamespaces = $xLibConfig->getOptionNames('views')))
        {
            foreach($aNamespaces as $sNamespace => $sOption)
            {
                // If no default namespace is defined, use the first one as default.
                if($this->sDefaultNamespace === '')
                {
                    $this->sDefaultNamespace = (string)$sNamespace;
                }
                // Save the namespace
                $aNamespace = $xLibConfig->getOption($sOption);
                $aNamespace['package'] = $sPackage;
                if(!isset($aNamespace['renderer']))
                {
                    $aNamespace['renderer'] = 'jaxon'; // 'jaxon' is the default renderer.
                }

                // If the lib config has defined a template option, then its value must be
                // read from the app config.
                if($xPkgConfig !== null && isset($aNamespace['template']))
                {
                    $sTemplateOption = $xLibConfig->getOption($sOption . '.template.option');
                    $sTemplateDefault = $xLibConfig->getOption($sOption . '.template.default');
                    $sTemplate = $xPkgConfig->getOption($sTemplateOption, $sTemplateDefault);
                    $aNamespace['directory'] = rtrim($aNamespace['directory'], '/') . '/' . $sTemplate;
                }

                $this->_addNamespace($sNamespace, $aNamespace);
            }
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
        return $this->di->get('jaxon.app.view.' . $sId);
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
        $this->di->set('jaxon.app.view.' . $sId, function($di) use ($sId, $xClosure) {
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
     * Get the view renderer for a given namespace
     *
     * @param string $sNamespace    The namespace name
     *
     * @return ViewInterface|null
     */
    public function getNamespaceRenderer(string $sNamespace): ?ViewInterface
    {
        $aNamespace = $this->getNamespace($sNamespace);
        if(!$aNamespace)
        {
            return null;
        }
        // Return the view renderer with the configured id
        return $this->getRenderer($aNamespace['renderer']);
    }
}
