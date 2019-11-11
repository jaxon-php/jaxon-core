<?php

namespace Jaxon\Response;

use Jaxon\Contracts\Response as ResponseContract;

class UploadResponse implements ResponseContract
{
    use \Jaxon\Features\Config;

    /**
     * The response type
     *
     * @var string
     */
    private $sContentType = 'text/html';

    /**
     * The path to the uploaded file
     *
     * @var string
     */
    private $sUploadedFile = '';

    /**
     * The error message
     *
     * @var string
     */
    private $sErrorMessage = '';

    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->sContentType;
    }

    /**
     * Set the path to the uploaded file
     */
    public function setUploadedFile($sUploadedFile)
    {
        $this->sUploadedFile = $sUploadedFile;
    }

    /**
     * Set the error message
     */
    public function setErrorMessage($sErrorMessage)
    {
        $this->sErrorMessage = $sErrorMessage;
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding()
    {
        return $this->getOption('core.encoding');
    }

    /**
     * Add a command to display a debug message to the user
     *
     * @param string        $sMessage            The message to be displayed
     *
     * @return \Jaxon\Plugin\Response
     */
    public function debug($sMessage)
    {
        // Todo: send this message to the console log.
    }

    /**
     * Used internally to generate the response headers
     *
     * @return void
     */
    public function sendHeaders()
    {
        $sCharacterSet = '';
        $sCharacterEncoding = trim($this->getOption('core.encoding'));
        if(($sCharacterEncoding) && strlen($sCharacterEncoding) > 0)
        {
            $sCharacterSet = '; charset="' . trim($sCharacterEncoding) . '"';
        }

        header('content-type: ' . $this->sContentType . ' ' . $sCharacterSet);
    }

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput()
    {
        $aResponse = ($this->sUploadedFile) ?
            ['code' => 'success', 'upl' => $this->sUploadedFile] :
            ['code' => 'error', 'msg' => $this->sErrorMessage];
        return '<script>var res = ' . json_encode($aResponse) . '; </script>';
    }

    /**
     * Print the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return void
     */
    public function printOutput()
    {
        print $this->getOutput();
    }
}
