<?php

namespace Jaxon\Response;

abstract class AbstractResponse
{
    use \Jaxon\Features\Config;

    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    abstract public function getContentType();

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding()
    {
        return trim($this->getOption('core.encoding'));
    }

    /**
     * Used internally to generate the response headers
     *
     * @return void
     */
    public function sendHeaders()
    {
        if(jaxon()->di()->getRequestHandler()->requestMethodIsGet())
        {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }

        $sCharacterSet = '';
        $sCharacterEncoding = $this->getCharacterEncoding();
        if(is_string($sCharacterEncoding) && strlen($sCharacterEncoding) > 0)
        {
            $sCharacterSet = '; charset="' . $sCharacterEncoding . '"';
        }

        header('content-type: ' . $this->getContentType() . ' ' . $sCharacterSet);
    }

    /**
     * Merge the response commands from the specified <Response> object with
     * the response commands in this <Response> object
     *
     * @param AbstractResponse|array    $mCommands  The <Response> object
     * @param boolean                   $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse($mCommands, $bBefore = false)
    {
    }

    /**
     * Add a command to display a debug message to the user
     *
     * @param string        $sMessage            The message to be displayed
     *
     * @return AbstractResponse
     */
    abstract public function debug($sMessage);

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    abstract public function getOutput();

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
