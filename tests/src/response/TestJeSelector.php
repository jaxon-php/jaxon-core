<?php

use Jaxon\App\FuncComponent;

class TestJeSelector extends FuncComponent
{
    public function html()
    {
        $this->response()->je('path1')->html('This is the html content');
        $this->response()->je('path2')->html('This is the html content');
        // Do nothing
        $this->response()->je('path1')->nothing(jo());
    }

    public function assign()
    {
        $this->response()->je('path1')->value = 'This is the html content';
        $this->response()->je('path3')->value = jq('#path2')->value;
        $this->response()->je('path3')->attr('name', jq('#path2')->attr('name'));
    }

    public function click()
    {
        $this->response()->je('path1')
            ->addEventListener('click', $this->rq()->html(jq()->attr('data-value')));
        $this->response()->je('path1')
            ->addEventListener('click', $this->rq()->html(jq('.path', '#context')));
        $this->response()->je('path1')
            ->addEventListener('click', jo('path2')->toggle());
    }
}
