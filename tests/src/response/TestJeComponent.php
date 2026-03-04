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
            ->ifeq(je('value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifteq()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifteq(je('value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifne()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifne(je('value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifnte()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifnte(je('value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifgt()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifgt(je('value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function ifge()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifge(je('value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function iflt()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->iflt(je('value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function ifle()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->ifle(je('value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function when()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->when(je('value')->checked)->elseShow('Success'));
    }

    public function unless()
    {
        $this->response()->je('target')->click($this->rq()->render()
            ->unless(je('value')->checked)->elseShow('Success'));
    }
}
