<?php

namespace Jaxon\Request\Call\Traits;

use Jaxon\Request\Call\Call;
use Jaxon\Request\Call\Parameter;

use function array_map;
use function func_get_args;

trait CallMessageTrait
{
    /**
     * The type of the message to show
     *
     * @var string
     */
    private $sMessageType = 'warning';

    /**
     * The arguments of the elseShow() call
     *
     * @var array
     */
    protected $aMessageArgs = [];

    /**
     * Set the message if the condition to the call is not met
     *
     * The first parameter is the message to show. The second allows inserting data from
     * the webpage in the message using positional placeholders.
     *
     * @param string $sMessageType  The message to show
     * @param array $aMessageArgs
     *
     * @return Call
     */
    private function setMessage(string $sMessageType, array $aMessageArgs): Call
    {
        $this->sMessageType = $sMessageType;
        $this->aMessageArgs = array_map(function($xParameter) {
            return Parameter::make($xParameter);
        }, $aMessageArgs);
        return $this;
    }

    /**
     * Show a message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseShow(string $sMessage): Call
    {
        return $this->setMessage('warning', func_get_args());
    }

    /**
     * Show an information message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseInfo(string $sMessage): Call
    {
        return $this->setMessage('info', func_get_args());
    }

    /**
     * Show a success message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseSuccess(string $sMessage): Call
    {
        return $this->setMessage('success', func_get_args());
    }

    /**
     * Show a warning message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseWarning(string $sMessage): Call
    {
        return $this->setMessage('warning', func_get_args());
    }

    /**
     * Show an error message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseError(string $sMessage): Call
    {
        return $this->setMessage('error', func_get_args());
    }
}
