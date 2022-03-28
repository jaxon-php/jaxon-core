<?php

use Jaxon\CallableClass;
use Jaxon\Response\Response;

class TestJs extends CallableClass
{
    public function redirect(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->redirect('http://example.test/path', 50);
        $this->response->redirect('http://example.test/path');
        return $this->response;
    }

    public function confirm(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->confirmCommands(2, 'Confirm?');
        return $this->response;
    }

    public function alert(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->alert('This is an alert!!');
        return $this->response;
    }

    public function debug(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->debug('This is a debug message!!');
        return $this->response;
    }

    public function script(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->script('console.debug("A debug message")');
        return $this->response;
    }

    public function call(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->call('console.debug', 'A debug message');
        return $this->response;
    }

    public function setEvent(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->setEvent('div', 'click', 'console.debug("A debug message")');
        return $this->response;
    }

    public function onClick(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->onClick('div', 'console.debug("A debug message")');
        return $this->response;
    }

    public function addHandler(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->addHandler('div', 'click', 'jsFunc');
        return $this->response;
    }

    public function removeHandler(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->removeHandler('div', 'click', 'jsFunc');
        return $this->response;
    }

    public function setFunction(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->setFunction('jsFunc', 'param1,param2', 'console.debug(param1, param2)');
        return $this->response;
    }

    public function wrapFunction(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->wrapFunction('jsFunc', 'param1,param2', ['let param3=param1+param2;',
            'return param3+param4;'], 'param4');
        return $this->response;
    }

    public function includeScript(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->includeScript('http://example.test/assets/js/utils.js');
        return $this->response;
    }

    public function includeScriptOnce(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->includeScriptOnce('http://example.test/assets/js/utils.js');
        return $this->response;
    }

    public function removeScript(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->removeScript('http://example.test/assets/js/utils.js');
        return $this->response;
    }

    public function includeCss(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->includeCSS('http://example.test/assets/css/utils.css');
        return $this->response;
    }

    public function removeCss(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->removeCSS('http://example.test/assets/css/utils.css');
        return $this->response;
    }

    public function waitForCss(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->waitForCSS(100);
        return $this->response;
    }

    public function waitFor(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->waitFor('jsVar', 100);
        return $this->response;
    }

    public function sleep(): Response
    {
        // $this->response = jaxon()->getResponse();
        $this->response->sleep(100);
        return $this->response;
    }
}
