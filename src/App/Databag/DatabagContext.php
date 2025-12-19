<?php

namespace Jaxon\App\Databag;

class DatabagContext
{
    /**
     * The constructor
     *
     * @param Databag $xDatabag
     * @param string $sName
     */
    public function __construct(protected Databag $xDatabag, protected string $sName)
    {}

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set(string $sKey, $xValue): void
    {
        $this->xDatabag->set($this->sName, $sKey, $xValue);
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function new(string $sKey, $xValue): void
    {
        $this->xDatabag->new($this->sName, $sKey, $xValue);
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get(string $sKey, $xValue = null): mixed
    {
        return $this->xDatabag->get($this->sName, $sKey, $xValue);
    }

    /**
     * @return self
     */
    public function clear(): self
    {
        $this->xDatabag->clear($this->sName);
        return $this;
    }
}
