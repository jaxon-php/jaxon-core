<?php

use Jaxon\App\CallableClass;
use Jaxon\Response\Response;

class TestDom extends CallableClass
{
    public function assign(): Response
    {
        $this->response->assign('div', 'innerHTML', 'This HTML content to assign');
        $this->response->assign('div', 'css.color', 'blue');
        return $this->response;
    }

    public function html(): Response
    {
        $this->response->html('div', 'This HTML content to assign');
        return $this->response;
    }

    public function append(): Response
    {
        $this->response->append('div', 'innerHTML', 'This HTML content to append');
        return $this->response;
    }

    public function prepend(): Response
    {
        $this->response->prepend('div', 'innerHTML', 'This HTML content to prepend');
        return $this->response;
    }

    public function replace(): Response
    {
        $this->response->replace('div', 'innerHTML', 'prepend', 'replace');
        return $this->response;
    }

    public function clear(): Response
    {
        $this->response->clear('div', 'innerHTML');
        return $this->response;
    }

    public function contextAssign(): Response
    {
        $this->response->contextAssign('innerHTML', 'This HTML content to assign');
        return $this->response;
    }

    public function contextAppend(): Response
    {
        $this->response->contextAppend('innerHTML', 'This HTML content to append');
        return $this->response;
    }

    public function contextPrepend(): Response
    {
        $this->response->contextPrepend('innerHTML', 'This HTML content to prepend');
        return $this->response;
    }

    public function contextClear(): Response
    {
        $this->response->contextClear('innerHTML');
        return $this->response;
    }

    public function remove(): Response
    {
        $this->response->remove('div');
        return $this->response;
    }

    public function create(): Response
    {
        $this->response->create('parent-id', 'div', 'elt-id');
        return $this->response;
    }

    public function insertBefore(): Response
    {
        $this->response->insertBefore('target-id', 'div', 'elt-id');
        return $this->response;
    }

    public function insert(): Response
    {
        $this->response->insert('target-id', 'div', 'elt-id');
        return $this->response;
    }

    public function insertAfter(): Response
    {
        $this->response->insertAfter('target-id', 'div', 'elt-id');
        return $this->response;
    }

    public function createInput(): Response
    {
        $this->response->createInput('parent-id', 'text', 'name', 'elt-id');
        return $this->response;
    }

    public function insertInput(): Response
    {
        $this->response->insertInput('target-id', 'text', 'name', 'elt-id');
        return $this->response;
    }

    public function insertInputAfter(): Response
    {
        $this->response->insertInputAfter('target-id', 'text', 'name', 'elt-id');
        return $this->response;
    }
}
