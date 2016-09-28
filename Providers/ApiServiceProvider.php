<?php namespace Mreschke\Api\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Provides Api services
 * @copyright 2015 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
*/
class ApiServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        // Define publishing rules
        $this->definePublishing();

        $isServer = $this->app['config']['api.server'];

        if ($isServer) {
            $app = $this->app;
            $app->group(['namespace' => 'Mreschke\Api\Http\Controllers'], function ($app) {
                require __DIR__.'/../Http/routes-server.php';
            });
        }

        // Load Views
        #$this->loadViewsFrom(__DIR__.'/../Views', 'api');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind
        #$this->app->bind('Mreschke\Api\Api', function() {
            #return new Api($this->app);
        #});

        // Bind aliases
        $this->app->alias('Mreschke\Api\Api', 'Mreschke\Api');
        $this->app->alias('Mreschke\Api\Api', 'Mreschke\Api\ApiInterface');

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../Config/api.php', 'mreschke.api');
    }

    /**
     * Define publishing rules
     *
     * @return void
     */
    private function definePublishing()
    {
        # App base path
        $path = realpath(__DIR__.'/../');

        // Config publishing rules
        // ./artisan vendor:publish --tag="mreschke.api.configs"
        $this->publishes([
            "$path/Config" => base_path('/config/mreschke'),
        ], 'mreschke.api.configs');

        // Seed publishing rules
        // ./artisan vendor:publish --tag="mrcore.wiki.seeds"
        /*$this->publishes([
            "$path/Database/Seeds" => base_path('/database/seeds'),
        ], 'mrcore.wiki.seeds');*/
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        // All deferred providers must include this provides() array
        return array('Mreschke\Api\Api');
    }
}
