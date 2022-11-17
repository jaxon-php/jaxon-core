<?php

use Jaxon\App\CallableClass;
use Jaxon\Response\Response;
use function Jaxon\jq;

class TestJQuery extends CallableClass
{
    public function html(): Response
    {
        $this->response->jq('#path1')->html('This is the html content');
        $this->response->jq('.path2', '#context')->html('This is the html content');
        return $this->response;
    }

    public function assign(): Response
    {
        $this->response->jq('#path1')->value = 'This is the html content';
        $this->response->jq('#path3')->value = jq('#path2')->value;
        $this->response->jq('#path3')->attr('name', jq('#path2')->attr('name'));
        return $this->response;
    }

    public function click(): Response
    {
        $this->response->jq('#path1')->click($this->rq()->html(jq()->attr('data-value')));
        $this->response->jq('#path1')->click($this->rq()->html(jq('.path', '#context')));
        return $this->response;
    }
}
