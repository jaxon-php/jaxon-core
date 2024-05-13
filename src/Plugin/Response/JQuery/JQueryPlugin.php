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
     * @var string
     */
    protected $jQueryNs;

    /**
     * True if the next selector is a command
     *
     * @var bool
     */
    protected $bCommand = true;

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
     * @param bool $bCommand
     *
     * @return JQueryPlugin
     */
    public function command(bool $bCommand): JQueryPlugin
    {
        $this->bCommand = $bCommand;
        return $this;
    }

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
        if($this->bCommand && $this->response() !== null)
        {
            $this->addCommand('jquery.call', ['selector' => $xSelector]);
        }
        // Reset the command value.
        $this->bCommand = true;
        return $xSelector;
    }
}
