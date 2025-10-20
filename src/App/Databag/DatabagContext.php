<?php

namespace Jaxon\App\Databag;

class DatabagContext
{
    /**
     * @var Databag
     */
    protected $xDatabag;

    /**
     * @var string
     */
    protected $sName;

    /**
     * The constructor
     *
     * @param Databag $xDatabag
     * @param string $sName
     */
    public function __construct(Databag $xDatabag, string $sName)
    {
        $this->xDatabag = $xDatabag;
        $this->sName = $sName;
    }

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
}
