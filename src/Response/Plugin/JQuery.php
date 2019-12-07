<?php

namespace Jaxon\Response\Plugin;

use Jaxon\Response\Plugin\JQuery\Dom\Element;

class JQuery extends \Jaxon\Plugin\Response
{
    use \Jaxon\Features\Config;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'jquery';
    }

    /**
     * @inheritDoc
     */
    public function getHash()
    {
        // Use the version number as hash
        return '3.3.0';
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript()
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
    public function element($sSelector = '', $sContext = '')
    {
        $xElement = new Element($sSelector, $sContext);
        $this->addCommand(['cmd' => 'jquery'], $xElement);
        return $xElement;
    }
}
