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
     * @var Container
     */
    protected $di;

    /**
     * @var DataBag
     */
    protected $xDataBag = null;

    /**
     * The constructor
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @return void
     */
    private function initDataBag()
    {
        if($this->xDataBag !== null)
        {
            return;
        }

        $xRequest = $this->di->getRequest();
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
        return is_string($xData) ?
            (json_decode($xData, true) ?: []) :
            (is_array($xData) ? $xData : []);
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
     * @return void
     */
    public function writeCommand()
    {
        $this->initDataBag();
        if($this->xDataBag->touched())
        {
            $this->addCommand('databag.set', ['values' => $this->xDataBag]);
        }
    }

    /**
     * @param string $sName
     *
     * @return DataBagContext
     */
    public function bag(string $sName): DataBagContext
    {
        $this->initDataBag();
        return new DataBagContext($this->xDataBag, $sName);
    }
}
