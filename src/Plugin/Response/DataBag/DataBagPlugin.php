<?php

namespace Jaxon\Plugin\Response\DataBag;

use Jaxon\App\DataBag\DataBag;
use Jaxon\App\DataBag\DataBagContext;
use Jaxon\Di\Container;
use Jaxon\Plugin\AbstractResponsePlugin;

use function is_array;
use function is_string;
use function json_decode;

class DataBagPlugin extends AbstractResponsePlugin
{
    /**
     * @const The plugin name
     */
    public const NAME = 'bags';

    /**
     * @var DataBag
     */
    protected $xDataBag = null;

    /**
     * The constructor
     */
    public function __construct(protected Container $di)
    {}

    /**
     * @return void
     */
    private function initDataBag(): void
    {
        if($this->xDataBag !== null)
        {
            return;
        }

        // Get the databag contents from the HTTP request parameters.
        $xRequest = $this->di->getRequest();
        $aBody = $xRequest->getParsedBody();
        $aParams = $xRequest->getQueryParams();
        $aData = is_array($aBody) ?
            $this->readData($aBody['jxnbags'] ?? []) :
            $this->readData($aParams['jxnbags'] ?? []);
        $this->xDataBag = new DataBag($this, $aData);
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
        // Todo: verify the checksums.
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
    public function writeCommand(): void
    {
        $this->initDataBag();
        if($this->xDataBag->touched())
        {
            // Todo: calculate the checksums.
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
