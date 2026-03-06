<?php

use Jaxon\App\NodeComponent;

class TestNode extends NodeComponent
{
    public function html(): string
    {
        return '';
    }

    public function assign()
    {
        $this->node()->assign('innerHTML', 'This HTML content to assign');
        $this->node()->assign('css.color', 'blue');
    }

    public function style()
    {
        $this->node()->html('This HTML content to assign');
        $this->node()->style('color', 'blue');
    }

    public function append()
    {
        $this->node()->append('innerHTML', 'This HTML content to append');
    }

    public function prepend()
    {
        $this->node()->prepend('innerHTML', 'This HTML content to prepend');
    }

    public function replace()
    {
        $this->node()->replace('innerHTML', 'prepend', 'replace');
    }

    // The clear() method already exists.
    public function clean()
    {
        $this->node()->clear();
    }

    public function remove()
    {
        $this->node()->remove();
    }
}
