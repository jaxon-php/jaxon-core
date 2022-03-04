<?php

namespace Jaxon\Response\Plugin;

use function is_string;
use function json_decode;
use function is_array;

class DataBag extends \Jaxon\Plugin\Response
{
    /**
     * @var DataBag\Bag
     */
    protected $xBag;

    /**
     * The constructor
     */
    public function __construct()
    {
        $aData = isset($_POST['jxnbags']) ? $this->readData($_POST) :
            (isset($_GET['jxnbags']) ? $this->readData($_GET) : []);
        $this->xBag = new DataBag\Bag($aData);
    }

    /**
     * @param array $aFrom
     *
     * @return array
     */
    private function readData(array $aFrom): array
    {
        // Todo: clean input data.
        if(is_string($aFrom['jxnbags']))
        {
            return json_decode($aFrom['jxnbags'], true) ?: [];
        }
        if(is_array($aFrom['jxnbags']))
        {
            return $aFrom['jxnbags'];
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'bags';
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        // Use the version number as hash
        return '1.0.0';
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript(): string
    {
        return '
    jaxon.command.handler.register("bags.set", function(args) {
        for (const bag in args.data) {
            jaxon.ajax.parameters.bags[bag] = args.data[bag];
        }
    });
';
    }

    /**
     * @return void
     */
    public function writeCommand()
    {
        if($this->xBag->touched())
        {
            $this->addCommand(['cmd' => 'bags.set'], $this->xBag->getAll());
        }
    }

    /**
     * @param string $sName
     *
     * @return DataBag\Context
     */
    public function bag(string $sName): DataBag\Context
    {
        return new DataBag\Context($this->xBag, $sName);
    }
}
