<?php

namespace Jaxon\Response;

class UploadResponse extends AbstractResponse
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
     * @return AbstractResponse
     */
    public function debug($sMessage)
    {
        // Todo: send this message to the console log.
        return $this;
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
}
