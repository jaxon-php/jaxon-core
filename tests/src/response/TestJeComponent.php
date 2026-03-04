<?php

use Jaxon\App\NodeComponent;

class TestJeComponent extends NodeComponent
{
    public function html(): string
    {
        return '';
    }

    public function confirm()
    {
        $this->response()->je('target')->click($this->rq()->render()->confirm('Do?'));
    }

    public function confirmElseWarning()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->confirm('Do?')->elseWarning('Warning'));
    }

    public function confirmElseError()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->confirm('Do?')->elseError('Error'));
    }

    public function ifeq()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifeq('1', 1)->elseInfo('Info'));
    }

    public function ifteq()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifteq('1', 1)->elseInfo('Info'));
    }

    public function ifne()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifne('1', 1)->elseInfo('Info'));
    }

    public function ifnte()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifnte('1', 1)->elseInfo('Info'));
    }

    public function ifgt()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifgt(10, 1)->elseSuccess('Success'));
    }

    public function ifge()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifge(10, 1)->elseSuccess('Success'));
    }

    public function iflt()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->iflt(10, 1)->elseSuccess('Success'));
    }

    public function ifle()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifle(10, 1)->elseSuccess('Success'));
    }
}
