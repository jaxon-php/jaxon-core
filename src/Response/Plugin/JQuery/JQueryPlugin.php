<?php

namespace Jaxon\Response\Plugin\JQuery;

use Jaxon\Plugin\ResponsePlugin;

class JQueryPlugin extends ResponsePlugin
{
    /**
     * @var string
     */
    protected $jQueryNs;

    /**
     * The class constructor
     *
     * @param string $jQueryNs
     */
    public function __construct(string $jQueryNs)
    {
        $this->jQueryNs = $jQueryNs;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'jquery';
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
     * @inheritDoc
     */
    public function getReadyScript(): string
    {
        return '
    jaxon.command.handler.register("jquery", function(args) {
        jaxon.cmd.script.execute(args);
    });
';
    }

    /**
     * Create a JQueryPlugin DomSelector, and link it to the current response.
     *
     * Since this element is linked to a response, its code will be automatically sent to the client.
     * The returned object can be used to call jQuery functions on the selected elements.
     *
     * @param string $sPath    The jQuery selector path
     * @param string $sContext    A context associated to the selector
     *
     * @return DomSelector
     */
    public function selector(string $sPath = '', string $sContext = ''): DomSelector
    {
        $xSelector = new DomSelector($this->jQueryNs, $sPath, $sContext);
        if($this->xResponse !== null)
        {
            $this->addCommand(['cmd' => 'jquery'], $xSelector);
        }
        return $xSelector;
    }
}
