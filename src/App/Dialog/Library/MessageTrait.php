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
use Jaxon\Request\Call\Parameter;

use function array_map;

trait MessageTrait
{
    /**
     * The next message title
     *
     * @var string
     */
    private $sTitle = '';

    /**
     * Set the title of the next message.
     *
     * @param string $sTitle     The title of the message
     *
     * @return MessageInterface
     */
    public function title(string $sTitle): MessageInterface
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
            'type' => $sType,
            'message' => [
                'title' => $sTitle,
                'phrase' => [
                    'str' => $sMessage,
                    'args' => array_map(fn($xArg) => Parameter::make($xArg), $aArgs),
                ],
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
