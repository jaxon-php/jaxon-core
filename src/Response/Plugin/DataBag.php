<?php

namespace Jaxon\Response\Plugin;

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
        // Todo: clean input data.
        $aData = [];
        if(isset($_POST['jxnbags']) && is_array($_POST['jxnbags']))
        {
            $aData = $_POST['jxnbags'];
        }
        elseif(isset($_GET['jxnbags']) && is_array($_GET['jxnbags']))
        {
            $aData = $_GET['jxnbags'];
        }
        $this->xBag = new DataBag\Bag($aData);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'bags';
    }

    /**
     * @inheritDoc
     */
    public function getHash()
    {
        // Use the version number as hash
        return '1.0.0';
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript()
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
     * @return bool
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
     * @return DataBag\Bag
     */
    public function bag($sName)
    {
        return $this->xBag->setName($sName);
    }
}
