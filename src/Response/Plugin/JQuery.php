<?php

namespace Jaxon\Response\Plugin;

use Jaxon\Response\Plugin\JQuery\Dom\Element;
use Jaxon\Utils\Config\Config;

class JQuery extends \Jaxon\Plugin\Response
{
    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * The class constructor
     *
     * @param Config $xConfig
     */
    public function __construct(Config $xConfig)
    {
        $this->xConfig = $xConfig;
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
        return '3.3.0';
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
     * Create a JQuery Element with a given selector, and link it to the current response.
     *
     * Since this element is linked to a response, its code will be automatically sent to the client.
     * The returned object can be used to call jQuery functions on the selected elements.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return Element
     */
    public function element(string $sSelector = '', string $sContext = ''): Element
    {
        $jQueryNs = $this->xConfig->getOption('core.jquery.no_conflict', false) ? 'jQuery' : '$';
        $xElement = new Element($jQueryNs, $sSelector, $sContext);
        $this->addCommand(['cmd' => 'jquery'], $xElement);
        return $xElement;
    }
}
