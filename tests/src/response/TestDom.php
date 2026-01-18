<?php

use Jaxon\App\FuncComponent;

class TestDom extends FuncComponent
{
    public function assign()
    {
        $this->response()->assign('div', 'innerHTML', 'This HTML content to assign');
        $this->response()->assign('div', 'css.color', 'blue');
    }

    public function html()
    {
        $this->response()->html('div', 'This HTML content to assign');
    }

    public function append()
    {
        $this->response()->append('div', 'innerHTML', 'This HTML content to append');
    }

    public function prepend()
    {
        $this->response()->prepend('div', 'innerHTML', 'This HTML content to prepend');
    }

    public function replace()
    {
        $this->response()->replace('div', 'innerHTML', 'prepend', 'replace');
    }

    public function clear()
    {
        $this->response()->clear('div', 'innerHTML');
    }

    public function remove()
    {
        $this->response()->remove('div');
    }

    public function create()
    {
        $this->response()->create('parent-id', 'div', 'elt-id');
    }

    public function insertBefore()
    {
        $this->response()->insertBefore('target-id', 'div', 'elt-id');
    }

    public function insert()
    {
        $this->response()->insert('target-id', 'div', 'elt-id');
    }

    public function insertAfter()
    {
        $this->response()->insertAfter('target-id', 'div', 'elt-id');
    }
}
