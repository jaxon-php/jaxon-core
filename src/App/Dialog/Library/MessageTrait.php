<?php

/**
 * MessageTrait.php - Show alert messages.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\App\Dialog\MessageInterface;

trait MessageTrait
{
    /**
     * The next message title
     *
     * @var string
     */
    private $sTitle = '';

    /**
     * Get the MessageInterface library
     *
     * @return MessageInterface
     */
    abstract public function getMessageLibrary(): MessageInterface;

    /**
     * Add a confirm question to a function call.
     *
     * @param string $sStr
     * @param array $aArgs
     *
     * @return array
     */
    abstract private function phrase(string $sStr, array $aArgs = []): array;

    /**
     * Set the title of the next message.
     *
     * @param string $sTitle     The title of the message
     *
     * @return self
     */
    public function title(string $sTitle)
    {
        $this->sTitle = $sTitle;

        return $this;
    }

    /**
     * Print an alert message.
     *
     * @param string $sType     The type of the message
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    private function alert(string $sType, string $sMessage, array $aArgs): array
    {
        $sTitle = $this->sTitle;
        $this->sTitle = '';

        return [
            'lib' => $this->getMessageLibrary()->getName(),
            'type' => $sType,
            'content' => [
                'title' => $sTitle,
                'phrase' => $this->phrase($sMessage, $aArgs),
            ],
        ];
    }

    /**
     * Show a success message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function success(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('success', $sMessage, $aArgs);
    }

    /**
     * Show an information message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function info(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('info', $sMessage, $aArgs);
    }

    /**
     * Show a warning message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function warning(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('warning', $sMessage, $aArgs);
    }

    /**
     * Show an error message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function error(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('error', $sMessage, $aArgs);
    }
}
