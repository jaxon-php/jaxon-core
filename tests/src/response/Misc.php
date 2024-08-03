<?php

use Jaxon\App\CallableClass;
use Jaxon\Response\Response;
use Jaxon\Upload\UploadResponse;
use function Jaxon\jaxon;

class Misc extends CallableClass
{
    public function simple(): Response
    {
        $this->response->alert('This is the global response!');
        return $this->response;
    }

    public function merge(): Response
    {
        $this->response->alert('This is the global response!');

        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
        return $xResponse;
    }

    public function appendbefore(): Response
    {
        $this->response->alert('This is the global response!');
        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
        // Merge responses. No need. In v5, the commands are automatically merged.
        // $this->response->appendResponse($xResponse, true);
        return $this->response;
    }

    public function mergeWithUpload(): UploadResponse
    {
        $this->response->alert('This is the global response!');
        $this->response->debug('Merging with a different response!');

        $di = jaxon()->di();
        $xResponse = new UploadResponse($di->getResponseManager(), $di->getPluginManager(), 'file.txt');
        return $xResponse;
    }
}
