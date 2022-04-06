<?php

namespace Jaxon\Plugin\Response\DataBag;

use Jaxon\Di\Container;
use Jaxon\Plugin\ResponsePlugin;

use function is_array;
use function is_string;
use function json_decode;

class DataBagPlugin extends ResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'bags';

    /**
     * @var DataBag
     */
    protected $xDataBag;

    /**
     * The constructor
     */
    public function __construct(Container $di)
    {
        $xRequest = $di->getRequest();
        $aBody = $xRequest->getParsedBody();
        $aParams = $xRequest->getQueryParams();
        $aData = is_array($aBody) ?
            $this->readData($aBody['jxnbags'] ?? []) :
            $this->readData($aParams['jxnbags'] ?? []);
        $this->xDataBag = new DataBag($aData);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param mixed $xData
     *
     * @return array
     */
    private function readData($xData): array
    {
        // Todo: clean input data.
        if(is_string($xData))
        {
            return json_decode($xData, true) ?: [];
        }
        return is_array($xData) ? $xData : [];
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
        if($this->xDataBag->touched())
        {
            $this->addCommand(['cmd' => 'bags.set'], $this->xDataBag->getAll());
        }
    }

    /**
     * @param string $sName
     *
     * @return DataBagContext
     */
    public function bag(string $sName): DataBagContext
    {
        return new DataBagContext($this->xDataBag, $sName);
    }
}
