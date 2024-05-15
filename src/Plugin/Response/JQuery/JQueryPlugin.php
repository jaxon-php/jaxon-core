<?php

namespace Jaxon\Plugin\Response\JQuery;

use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Js\Selector;

class JQueryPlugin extends ResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'jquery';

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        // Use the version number as hash
        return '4.0.0';
    }

    /**
     * Create a JQueryPlugin Selector, and link it to the current response.
     *
     * Since this element is linked to a response, its code will be automatically sent to the client.
     * The returned object can be used to call jQuery functions on the selected elements.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     *
     * @return Selector
     */
    public function selector(string $sPath = '', $xContext = null): Selector
    {
        $xSelector = new Selector($sPath, $xContext);
        $this->addCommand('jquery.call', ['selector' => $xSelector]);
        return $xSelector;
    }
}
