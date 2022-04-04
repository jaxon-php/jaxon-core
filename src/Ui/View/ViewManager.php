<?php

namespace Jaxon\Ui\View;

use Jaxon\Di\Container;
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
        if(!isset($this->aNamespaces[$sNamespace]))
        {
            return null;
        }
        // Return the view renderer with the configured id
        return $this->getRenderer($this->aNamespaces[$sNamespace]['renderer']);
    }
}
