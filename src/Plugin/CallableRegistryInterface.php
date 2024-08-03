<?php

namespace Jaxon\Plugin;

interface CallableRegistryInterface
{
    /**
     * Check if the provided options are correct, and convert them into an array.
     *
     * @param string $sCallable
     * @param mixed $xOptions
     *
     * @return array
     */
    public function checkOptions(string $sCallable, $xOptions): array;

    /**
     * Register a callable entity: a function or a class.
     *
     * Called by the <Jaxon\Plugin\RequestManager> when a user script
     * when a function or callable object is to be registered.
     * Additional plugins may support other registration types.
     *
     * @param string $sType    The type of request handler being registered
     * @param string $sCallable    The callable entity being registered
     * @param array $aOptions    The associated options
     *
     * @return bool
     */
    public function register(string $sType, string $sCallable, array $aOptions): bool;

    /**
     * Get the callable object for a registered item
     *
     * @param string $sCallable
     *
     * @return mixed
     */
    public function getCallable(string $sCallable);
}
