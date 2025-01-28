<?php

use Jaxon\App\CallableClass;
use function Jaxon\jaxon;
use function Jaxon\js;
use function Jaxon\jw;

class Misc extends CallableClass
{
    public function simple()
    {
        $this->response->alert('This is the global response!');
        $aCommands = $this->response->getCommands();
        $aCommands[0]->setOption('name1', 'value1');
        $aCommands[0]->setOption('name2', 'value2');
    }

    public function merge()
    {
        $this->response->alert('This is the global response!');

        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
    }

    public function appendbefore()
    {
        $this->response->alert('This is the global response!');
        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
    }

    public function commands()
    {
        // Create a DOM node
        $this->response->create('parent', 'div', 'child');
        // Insert a DOM node before an other
        $this->response->insertBefore('sibling', 'div', 'new');
        // Insert a DOM node after an other
        $this->response->insertAfter('sibling', 'div', 'new');
        // Add an event handler on the target node
        $this->response->addEventHandler('target', 'click', js('console')->log('Clicked!!'));
        // Bind the target to a component
        $this->response->bind('target', $this->rq('TestComponent'));
        // Bind the target to a component with item
        $this->response->bind('target', $this->rq('TestComponent'), 'item');
    }

    public function jsCommands()
    {
        $this->response->js('console')->log('Debug message');
        $this->response->exec(js('console')->log('Debug message'));
        $this->response->exec(jw()->console->log('Debug message'));
    }

    public function paginate(int $page = 0)
    {
        $this->response->paginator($page, 10, 25)
            ->render($this->rq()->paginate(), 'pagination');
    }
}
