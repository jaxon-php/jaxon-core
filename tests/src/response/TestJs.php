<?php

use Jaxon\App\CallableClass;

use function Jaxon\js;

class TestJs extends CallableClass
{
    public function redirect()
    {
        $this->response->redirect('http://example.test/path', 50);
        $this->response->redirect('http://example.test/path');
    }

    public function confirm()
    {
        $this->response->confirm(function($resp) {
            $resp->debug('This is the first debug message!!');
            $resp->debug('This is the second debug message!!');
        }, 'Confirm?');
    }

    public function alert()
    {
        $this->response->alert('This is an alert!!');
    }

    public function debug()
    {
        $this->response->debug('This is a debug message!!');
    }

    public function call()
    {
        $this->response->call('console.debug', 'A debug message');
    }

    public function setEvent()
    {
        $this->response->setEventHandler('div', 'click', js('console')->debug("A debug message"));
    }

    public function onClick()
    {
        $this->response->onClick('div', js('console')->debug("A debug message"));
    }

    public function addHandler()
    {
        $this->response->addHandler('div', 'click', 'jsFunc');
    }

    public function removeHandler()
    {
        $this->response->removeHandler('div', 'click', 'jsFunc');
    }

    public function sleep()
    {
        $this->response->sleep(100);
    }
}
