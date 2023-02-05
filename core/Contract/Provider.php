<?php

namespace Core\Contract;

/**
 * Provider contract.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
interface Provider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register();

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot();
}
