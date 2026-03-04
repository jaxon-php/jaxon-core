<?php

use Jaxon\App\FuncComponent;

class TestJqSelector extends FuncComponent
{
    public function html()
    {
        $this->response()->jq('#path1')->html('This is the html content');
        $this->response()->jq('.path2', '#context')->html('This is the html content');
        // Do nothing
        $this->response()->jq('#path1');
    }

    public function assign()
    {
        $this->response()->jq('#path1')->__set('value', 'This is the html content');
        $this->response()->jq('#path3')->__set('value', jq('#path2')->value);
        $this->response()->jq('#path3')->attr('name', jq('#path2')->attr('name'));
    }

    public function click()
    {
        $this->response()->jq('#path1')->on('click', $this->rq()->html(jq()->attr('data-value')));
        $this->response()->jq('#path1')->on('click', $this->rq()->html(jq('.path', '#context')));
        // The jq('#path2')->toggle() call in the following is a callback.
        $this->response()->jq('#path1')->on('click', jq('#path2')->toggle());
    }
}
