<?php

namespace Jaxon\App\Component;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class Logger
{
    use LoggerTrait;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(private LoggerInterface $logger)
    {}

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
