<?php

namespace Jaxon\Plugin\Response\Databag;

use Jaxon\App\Databag\Databag;
use Jaxon\App\Databag\DatabagContext;
use Jaxon\Di\Container;
use Jaxon\Plugin\AbstractResponsePlugin;

use function is_array;
use function is_string;
use function json_decode;

class DatabagPlugin extends AbstractResponsePlugin
{
    /**
     * @const The plugin name
     */
    public const NAME = 'bags';

    /**
     * @var Databag
     */
    protected $xDatabag = null;

    /**
     * The constructor
     */
    public function __construct(protected Container $di)
    {}

    /**
     * @return void
     */
    private function initDatabag(): void
    {
        if($this->xDatabag !== null)
        {
            return;
        }

        // Get the databag contents from the HTTP request parameters.
        $aBags = $this->di->getRequest()->getAttribute('jxnbags', []);
        $this->xDatabag = new Databag($this, $this->readData($aBags));
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
        $this->initDatabag();
        if($this->xDatabag->touched())
        {
            // Todo: calculate the checksums.
            $this->addCommand('databag.set', ['values' => $this->xDatabag]);
        }
    }

    /**
     * @param string $sName
     *
     * @return DatabagContext
     */
    public function bag(string $sName): DatabagContext
    {
        $this->initDatabag();
        return new DatabagContext($this->xDatabag, $sName);
    }
}
