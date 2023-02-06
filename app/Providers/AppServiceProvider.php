<?php

namespace App\Providers;

use Core\Contract\Provider;
use Core\Support\Facades\Log;
use Core\Support\Facades\Request;
use Core\Support\Facades\Session;
use Core\Support\Facades\Storage;
use Core\Provider\ServiceProvider;

class AppServiceProvider extends ServiceProvider implements Provider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // bind singleton class to container
        $this->app->singleton(Request::class);
        $this->app->singleton(Session::class);
        $this->app->singleton(Storage::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerShutdownFunction();
    }

    private function registerShutdownFunction()
    {
        register_shutdown_function(function() {
            if ($error = get_last_fatal_error()) {
                $logger = Log::instance(true);
                $logger->name('app_error' . date('Y-m-d'));
                $logger->error([
                    'message' => "{$error['file']}:{$error['line']}: {$error['message']}",
                    'caller' => [
                        'origin' => php_sapi_name() === 'cli' ? ("cli: ". $_SERVER['_'] . " " .realpath($_SERVER['SCRIPT_FILENAME'])) : Request::fullUrl(),
                        'header' => Request::header(),
                        'body'   => php_sapi_name() === 'cli' ? $_SERVER['argv'] : (Request::all() ?: Request::json()),
                    ],
                ]);
            }
        });
    }
}
