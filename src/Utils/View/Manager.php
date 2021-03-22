<?php

namespace Jaxon\Utils\View;

use Jaxon\Utils\Config\Config;

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
     * Get the view namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->aNamespaces;
    }

    /**
     * Find a view namespace by its name.
     *
     * @param string        $sNamespace         The namespace name
     *
     * @return array|null
     */
    public function getNamespace($sNamespace)
    {
        return \array_key_exists($sNamespace, $this->aNamespaces) ?
            $this->aNamespaces[$sNamespace] : null;
    }

    /**
     * Add a view namespace, and set the corresponding renderer.
     *
     * @param string        $sNamespace         The corresponding renderer name
     * @param array         $aNamespace         The namespace options
     *
     * @return void
     */
    private function _addNamespace($sNamespace, array $aNamespace)
    {
        $this->aNamespaces[$sNamespace] = $aNamespace;
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
            'directory' => $sDirectory,
            'extension' => $sExtension,
            'renderer' => $sRenderer,
        ];
        $this->_addNamespace($sNamespace, $aNamespace);
    }

    /**
     * Set the view namespaces.
     *
     * @param Config    $xLibConfig     The config options provided in the library
     * @param Config    $xAppConfig     The config options provided in the app section of the global config file.
     *
     * @return void
     */
    public function addNamespaces($xLibConfig, $xAppConfig = null)
    {
        $this->sDefaultNamespace = $xLibConfig->getOption('options.views.default', '');

        $sPackage = $xLibConfig->getOption('package', '');

        if(\is_array($aNamespaces = $xLibConfig->getOptionNames('views')))
        {
            foreach($aNamespaces as $sNamespace => $sOption)
            {
                // If no default namespace is defined, use the first one as default.
                if($this->sDefaultNamespace == '')
                {
                    $this->sDefaultNamespace = (string)$sNamespace;
                }
                // Save the namespace
                $aNamespace = $xLibConfig->getOption($sOption);
                $aNamespace['package'] = $sPackage;
                if(!\array_key_exists('renderer', $aNamespace))
                {
                    $aNamespace['renderer'] = 'jaxon'; // 'jaxon' is the default renderer.
                }

                // If the lib config has defined a template option, then its value must be
                // read from the app config.
                if($xAppConfig !== null && \array_key_exists('template', $aNamespace))
                {
                    $sTemplateOption = $xLibConfig->getOption($sOption . '.template.option');
                    $sTemplateDefault = $xLibConfig->getOption($sOption . '.template.default');
                    $sTemplate = $xAppConfig->getOption($sTemplateOption, $sTemplateDefault);
                    $aNamespace['directory'] = \rtrim($aNamespace['directory'], '/') . '/' . $sTemplate;
                }

                $this->_addNamespace($sNamespace, $aNamespace);
            }
        }
    }

    /**
     * Get the view renderer
     *
     * @param string        $sId        The unique identifier of the view renderer
     *
     * @return \Jaxon\Contracts\View
     */
    public function getRenderer($sId)
    {
        // Return the view renderer with the given id
        return jaxon()->di()->get('jaxon.app.view.' . $sId);
    }

    /**
     * Add a view renderer with an id
     *
     * @param string        $sId        The unique identifier of the view renderer
     * @param Closure       $xClosure   A closure to create the view instance
     *
     * @return void
     */
    public function addRenderer($sId, Closure $xClosure)
    {
        // Return the initialized view renderer
        jaxon()->di()->set('jaxon.app.view.' . $sId, function($di) use ($sId, $xClosure) {
            // Get the defined renderer
            $xRenderer = call_user_func($xClosure, $di);

            // Init the renderer with the template namespaces
            $aNamespaces = \array_filter($this->aNamespaces, function($aNamespace) use($sId) {
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
     * @param string        $sNamespace         The namespace name
     *
     * @return \Jaxon\Contracts\View|null
     */
    public function getNamespaceRenderer($sNamespace)
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
