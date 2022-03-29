<?php

namespace Jaxon\Response;

use function array_walk;
use function addslashes;
use function json_encode;

class UploadResponse implements ResponseInterface
{
    use Traits\CommandTrait;

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
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * Set the path to the uploaded file
     *
     * @param string  $sUploadedFile
     *
     * @return void
     */
    public function setUploadedFile(string $sUploadedFile)
    {
        $this->sUploadedFile = $sUploadedFile;
    }

    /**
     * Set the error message
     *
     * @param string  $sErrorMessage
     *
     * @return void
     */
    public function setErrorMessage(string $sErrorMessage)
    {
        $this->sErrorMessage = $sErrorMessage;
    }

    /**
     * @inheritDoc
     */
    public function debug(string $sMessage): ResponseInterface
    {
        $this->aDebugMessages[] = $sMessage;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOutput(): string
    {
        $aResponse = ($this->sUploadedFile) ?
            ['code' => 'success', 'upl' => $this->sUploadedFile] : ['code' => 'error', 'msg' => $this->sErrorMessage];

        $sConsoleLog = '';
        array_walk($this->aDebugMessages, function($sMessage) use (&$sConsoleLog) {
            $sConsoleLog .= '
    console.log("' . addslashes($sMessage) . '");';
        });

        return '
<!DOCTYPE html>
<html>
<body>
<h1>HTTP Upload for Jaxon</h1>
<p>No real data.</p>
</body>
<script>
    res = ' . json_encode($aResponse) . ';' . $sConsoleLog . '
</script>
</html>
';
    }
}
