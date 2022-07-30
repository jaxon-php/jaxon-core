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
        // Merge responses
        $this->response->appendResponse($xResponse, true);
        return $this->response;
    }

    public function mergeWithUpload(): UploadResponse
    {
        $this->response->alert('This is the global response!');

        $xResponse = new UploadResponse(jaxon()->di()->getPsr17Factory(), 'file.txt');
        $xResponse->debug('This is a different response!');
        return $xResponse;
    }
}
