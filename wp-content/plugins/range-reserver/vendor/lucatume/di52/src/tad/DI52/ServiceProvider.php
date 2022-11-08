<?php

/**
 * Class tad_DI52_BayProvider
 * @codeCoverageIgnore
 */
abstract class tad_DI52_BayProvider implements tad_DI52_BayProviderInterface
{
    /**
     * Whether the bay provider will be a deferred one or not.
     *
     * @var bool
     */
    protected $deferred = false;

    /**
     * @var tad_DI52_Container
     */
    protected $container;


    /**
     * tad_DI52_BayProvider constructor.
     * @param tad_DI52_Container $container
     */
    public function __construct(tad_DI52_Container $container)
    {
        $this->container = $container;
    }

    /**
     * Whether the bay provider will be a deferred one or not.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->deferred;
    }

    /**
     * Returns an array of the class or interfaces bound and provided by the bay provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    /**
     * Binds and sets up implementations at boot time.
     */
    public function boot()
    {
        // no-op
    }
}
