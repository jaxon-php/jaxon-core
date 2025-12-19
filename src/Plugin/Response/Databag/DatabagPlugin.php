<?php

namespace Jaxon\Plugin\Response\Databag;

use Jaxon\App\Databag\Databag;
use Jaxon\App\Databag\DatabagContext;
use Jaxon\Plugin\AbstractResponsePlugin;
use Closure;

use function array_map;
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
     * @var array<DatabagContext>
     */
    protected array $aContext = [];

    /**
     * The constructor
     *
     * @param Closure $fData
     */
    public function __construct(private Closure $fData)
    {}

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return array
     */
    private function readData(): array
    {
        // Todo: clean input data.
        // Todo: verify the checksums.
        $xData = ($this->fData)();
        $aData = is_string($xData) ?
            (json_decode($xData, true) ?: []) :
            (is_array($xData) ? $xData : []);
        // Ensure all contents are arrays.
        return array_map(fn($aValue) => is_array($aValue) ? $aValue : [], $aData);
    }

    /**
     * @return Databag
     */
    private function databag(): Databag
    {
        return $this->xDatabag ?? $this->xDatabag = new Databag($this->readData());
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
        $xDatabag = $this->databag();
        if($xDatabag->touched())
        {
            // Todo: calculate the checksums.
            $this->addCommand('databag.set', ['values' => $xDatabag]);
        }
    }

    /**
     * @param string $sName
     *
     * @return DatabagContext
     */
    public function bag(string $sName): DatabagContext
    {
        // The contexts are saved and reused.
        return $this->aContext[$sName] ??
            $this->aContext[$sName] = new DatabagContext($this->databag(), $sName);
    }
}
