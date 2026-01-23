<?php

use Jaxon\App\FuncComponent;

class Misc extends FuncComponent
{
    public function simple()
    {
        $this->response()->alert('This is the global response!');
        $aCommands = $this->response()->getCommands();
        $aCommands[0]->setOption('name1', 'value1');
        $aCommands[0]->setOption('name2', 'value2');
    }

    public function merge()
    {
        $this->response()->alert('This is the global response!');

        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
    }

    public function appendBefore()
    {
        $this->response()->alert('This is the global response!');
        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
    }

    public function commands()
    {
        // Create a DOM node
        $this->response()->create('parent', 'div', 'child');
        // Insert a DOM node before an other
        $this->response()->insert('sibling', 'div', 'new');
        $this->response()->insertBefore('sibling', 'div', 'new');
        // Insert a DOM node after an other
        $this->response()->insertAfter('sibling', 'div', 'new');
        // Add an event handler on the target node
        $this->response()->addEventHandler('target', 'click', jo('console')->log('Clicked!!'));
        // Bind the target to a component
        $this->response()->bind('target', $this->rq('TestComponent'));
        // Bind the target to a component with item
        $this->response()->bind('target', $this->rq('TestComponent'), 'item');
    }

    public function jsCommands()
    {
        $this->response()->jo('console')->log('Debug message');
        $this->response()->jo()->console->log('Debug message');
        $this->response()->exec(jo('console')->log('Debug message'));
        $this->response()->exec(jo()->console->log('Debug message'));
    }

    public function paginate(int $page = 0)
    {
        $this->paginator($page, 10, 25)
            ->render($this->rq()->paginate(), 'pagination');
    }
}
