<?php

use Jaxon\App\NodeComponent;

class TestJqComponent extends NodeComponent
{
    public function html(): string
    {
        return '';
    }

    public function confirm()
    {
        $this->response()->jq('#target')->click($this->rq()->render()->confirm('Do?'));
    }

    public function confirmElseWarning()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->confirm('Do?')->elseWarning('Warning'));
    }

    public function confirmElseError()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->confirm('Do?')->elseError('Error'));
    }

    public function ifeq()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifeq(jq('#value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifteq()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifteq(jq('#value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifne()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifne(jq('#value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifnte()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifnte(jq('#value')->val()->trim(), 'value')->elseInfo('Info'));
    }

    public function ifgt()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifgt(jq('#value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function ifge()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifge(jq('#value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function iflt()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->iflt(jq('#value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function ifle()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->ifle(jq('#value')->val()->toInt(), 1)->elseSuccess('Success'));
    }

    public function when()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->when(jq('#value')->checked)->elseShow('Success'));
    }

    public function unless()
    {
        $this->response()->jq('#target')->click($this->rq()->render()
            ->unless(jq('#value')->checked)->elseShow('Success'));
    }
}
