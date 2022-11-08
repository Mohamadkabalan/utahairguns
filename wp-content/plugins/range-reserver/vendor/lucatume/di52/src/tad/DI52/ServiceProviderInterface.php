<?php

interface tad_DI52_BayProviderInterface
{
    /**
     * Binds and sets up implementations.
     */
    public function register();

    /**
     * Binds and sets up implementations at boot time.
     */
    public function boot();

    /**
     * Returns an array of implementations provided by the bay provider.
     *
     * @return array
     */
    public function provides();

    /**
     * Whether the bay provider will be a deferred one or not.
     *
     * @return bool
     */
    public function isDeferred();
}
