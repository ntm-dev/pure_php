<?php

namespace Core\Provider;

use Core\Application;

abstract class ServiceProvider
{
    protected $app;
    protected bool $defer;

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->defer;
    }

    public function setApplicationContainer(Application $app): void
    {
        $this->app = $app;
    }
}
