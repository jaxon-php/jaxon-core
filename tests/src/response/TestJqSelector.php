<?php

use Jaxon\App\FuncComponent;

class TestJqSelector extends FuncComponent
{
    public function html()
    {
        $this->response()->jq('#path1')->html('This is the html content');
        $this->response()->jq('.path2', '#context')->html('This is the html content');
        // Do nothing
        $this->response()->jq('#path1')->nothing(jq());
    }

    public function assign()
    {
        $this->response()->jq('#path1')->value = 'This is the html content';
        $this->response()->jq('#path3')->value = jq('#path2')->value;
        $this->response()->jq('#path3')->attr('name', jq('#path2')->attr('name'));
    }

    public function click()
    {
        $this->response()->jq('#path1')
            ->on('click', $this->rq()->html(jq()->attr('data-value')));
        $this->response()->jq('#path1')
            ->on('click', $this->rq()->html(jq('.path', '#context')));
        $this->response()->jq('#path1')
            ->on('click', jq('#path2')->toggle());
    }
}
