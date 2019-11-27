<?php

namespace Jaxon\Response;

class UploadResponse extends AbstractResponse
{
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
     * The debug messages
     *
     * @var array
     */
    private $aDebugMessages = [];

    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/html';
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
     * Add a command to display a debug message to the user
     *
     * @param string        $sMessage            The message to be displayed
     *
     * @return UploadResponse
     */
    public function debug($sMessage)
    {
        $this->aDebugMessages[] = $sMessage;
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
            ['code' => 'success', 'upl' => $this->sUploadedFile] : ['code' => 'error', 'msg' => $this->sErrorMessage];

        $sConsoleLog = '';
        array_walk($this->aDebugMessages, function($sMessage) use (&$sConsoleLog) {
            $sConsoleLog .= '
    console.log("' . addslashes($sMessage) . '");';
        });

        return '
<script>
    var res = ' . json_encode($aResponse) . ';' . $sConsoleLog . '
</script>
';
    }
}
