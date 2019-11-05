<?php

namespace Jaxon\Utils\View;

use Jaxon\Config\Config;

use stdClass;
use Exception;
use Closure;

class Manager
{
    /**
     * The default namespace
     *
     * @var string
     */
    protected $sDefaultNamespace = '';

    /**
     * The view renderers
     *
     * @var array
     */
    protected $aRenderers = [];

    /**
     * The view namespaces
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * Get the default namespace
     *
     * @return string
     */
    public function getDefaultNamespace()
    {
        return $this->sDefaultNamespace;
    }

    /**
     * Get the view renderers
     *
     * @return array
     */
    public function getRenderers()
    {
        return $this->aRenderers;
    }

    /**
     * Get the view namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->aNamespaces;
    }

    /**
     * Add a view namespace, and set the corresponding renderer.
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     * @param string        $sRenderer          The corresponding renderer name
     *
     * @return void
     */
    public function addNamespace($sNamespace, $sDirectory, $sExtension, $sRenderer)
    {
        $aNamespace = [
            'namespace' => $sNamespace,
            'directory' => $sDirectory,
            'extension' => $sExtension,
        ];
        if(key_exists($sRenderer, $this->aNamespaces))
        {
            $this->aNamespaces[$sRenderer][] = $aNamespace;
        }
        else
        {
            $this->aNamespaces[$sRenderer] = [$aNamespace];
        }
        $this->aRenderers[$sNamespace] = $sRenderer;
    }

    /**
     * Set the view namespaces.
     *
     * @param Config            $xAppConfig             The application config options
     *
     * @return void
     */
    public function addNamespaces($xAppConfig)
    {
        $this->sDefaultNamespace = $xAppConfig->getOption('options.views.default', false);
        if(is_array($namespaces = $xAppConfig->getOptionNames('views')))
        {
            foreach($namespaces as $namespace => $option)
            {
                // If no default namespace is defined, use the first one as default.
                if($this->sDefaultNamespace == false)
                {
                    $this->sDefaultNamespace = $namespace;
                }
                // Save the namespace
                $directory = $xAppConfig->getOption($option . '.directory');
                $extension = $xAppConfig->getOption($option . '.extension', '');
                $renderer = $xAppConfig->getOption($option . '.renderer', 'jaxon');
                $this->addNamespace($namespace, $directory, $extension, $renderer);
            }
        }
    }

    /**
     * Get the view renderer facade
     *
     * @param string                $sId                The unique identifier of the view renderer
     *
     * @return object        The view renderer
     */
    public function getRenderer($sId = '')
    {
        if(!$sId)
        {
            // Return the view renderer facade
            return jaxon()->di()->get(\Jaxon\Utils\View\Renderer::class);
        }
        // Return the view renderer with the given id
        return jaxon()->di()->get('jaxon.app.view.' . $sId);
    }

    /**
     * Add a view renderer with an id
     *
     * @param string                $sId                The unique identifier of the view renderer
     * @param Closure               $xClosure           A closure to create the view instance
     *
     * @return void
     */
    public function addRenderer($sId, $xClosure)
    {
        // Return the non-initialiazed view renderer
        jaxon()->di()->set('jaxon.app.view.base.' . $sId, $xClosure);

        // Return the initialized view renderer
        jaxon()->di()->set('jaxon.app.view.' . $sId, function ($c) use ($sId) {
            // Get the defined renderer
            $renderer = $c['jaxon.app.view.base.' . $sId];

            // Init the renderer with the template namespaces
            if(key_exists($sId, $this->aNamespaces))
            {
                foreach($this->aNamespaces[$sId] as $ns)
                {
                    $renderer->addNamespace($ns['namespace'], $ns['directory'], $ns['extension']);
                }
            }
            return $renderer;
        });
    }
}
