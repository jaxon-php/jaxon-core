<?php

namespace Jaxon\Tests\Ns\Lib;

class Service implements ServiceInterface
{
    /**
     * @var string
     */
    private $source;

    public function __construct(array $config)
    {}

    public function action()
    {}

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }
}
