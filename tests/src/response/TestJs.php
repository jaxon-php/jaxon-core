<?php

use Jaxon\App\CallableClass;
use Jaxon\Response\Response;

use function Jaxon\js;

class TestJs extends CallableClass
{
    public function redirect(): Response
    {
        $this->response->redirect('http://example.test/path', 50);
        $this->response->redirect('http://example.test/path');
        return $this->response;
    }

    public function confirm(): Response
    {
        $this->response->confirm(function($resp) {
            $resp->debug('This is the first debug message!!');
            $resp->debug('This is the second debug message!!');
        }, 'Confirm?');
        return $this->response;
    }

    public function alert(): Response
    {
        $this->response->alert('This is an alert!!');
        return $this->response;
    }

    public function debug(): Response
    {
        $this->response->debug('This is a debug message!!');
        return $this->response;
    }

    public function call(): Response
    {
        $this->response->call('console.debug', 'A debug message');
        return $this->response;
    }

    public function setEvent(): Response
    {
        $this->response->setEventHandler('div', 'click', js('console')->debug("A debug message"));
        return $this->response;
    }

    public function onClick(): Response
    {
        $this->response->onClick('div', js('console')->debug("A debug message"));
        return $this->response;
    }

    public function addHandler(): Response
    {
        $this->response->addHandler('div', 'click', 'jsFunc');
        return $this->response;
    }

    public function removeHandler(): Response
    {
        $this->response->removeHandler('div', 'click', 'jsFunc');
        return $this->response;
    }

    public function sleep(): Response
    {
        $this->response->sleep(100);
        return $this->response;
    }
}
