<?php

namespace Jaxon\Response;

use function addslashes;
use function array_reduce;
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

    public function __construct(string $sUploadedFile, string $sErrorMessage = '')
    {
        $this->sUploadedFile = $sUploadedFile;
        $this->sErrorMessage = $sErrorMessage;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * Get the path to the uploaded file
     *
     * @return string
     */
    public function getUploadedFile(): string
    {
        return $this->sUploadedFile;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->sErrorMessage;
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
        $sResult = json_encode(($this->sUploadedFile) ?
            ['code' => 'success', 'upl' => $this->sUploadedFile] :
            ['code' => 'error', 'msg' => $this->sErrorMessage]) . ';';
        $sConsoleLog = array_reduce($this->aDebugMessages, function($sJsLog, $sMessage) {
            return "$sJsLog\n\t" . 'console.log("' . addslashes($sMessage) . '");';
        }, '');

        return '
<!DOCTYPE html>
<html>
<body>
<h1>HTTP Upload for Jaxon</h1>
</body>
<script>
    res = ' . $sResult . $sConsoleLog . '
</script>
</html>
';
    }
}
