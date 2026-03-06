<?php

use Jaxon\App\NodeComponent;

class TestRqComponent extends NodeComponent
{
    public function html(): string
    {
        return '';
    }

    public function confirm()
    {
        $this->response()->rq(TestJs::class)->message()->confirm('Do?');
    }

    public function confirmElseWarning()
    {
        $this->response()->rq(TestJs::class)->message()
            ->confirm('Do?')->elseWarning('Warning');
    }

    public function confirmElseError()
    {
        $this->response()->rq(TestJs::class)->message()
            ->confirm('Do?')->elseError('Error');
    }

    public function ifeq()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifeq(je('value')->val()->trim(), 'value')->elseInfo('Info');
    }

    public function ifteq()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifteq(je('value')->val()->trim(), 'value')->elseInfo('Info');
    }

    public function ifne()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifne(je('value')->val()->trim(), 'value')->elseInfo('Info');
    }

    public function ifnte()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifnte(je('value')->val()->trim(), 'value')->elseInfo('Info');
    }

    public function ifgt()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifgt(je('value')->val()->toInt(), 1)->elseSuccess('Success');
    }

    public function ifge()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifge(je('value')->val()->toInt(), 1)->elseSuccess('Success');
    }

    public function iflt()
    {
        $this->response()->rq(TestJs::class)->message()
            ->iflt(je('value')->val()->toInt(), 1)->elseSuccess('Success');
    }

    public function ifle()
    {
        $this->response()->rq(TestJs::class)->message()
            ->ifle(je('value')->val()->toInt(), 1)->elseSuccess('Success');
    }

    public function when()
    {
        $this->response()->rq(TestJs::class)->message()
            ->when(je('value')->checked)->elseShow('Success');
    }

    public function unless()
    {
        $this->response()->rq(TestJs::class)->message()
            ->unless(je('value')->checked)->elseShow('Success');
    }
}
