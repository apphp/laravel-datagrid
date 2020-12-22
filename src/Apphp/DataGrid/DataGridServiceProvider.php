<?php

namespace Apphp\DataGrid;

use Illuminate\Support\ServiceProvider;


class DataGridServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of this provider is deferred
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application events
     * @return void
     */
    public function boot()
    {
        // Prepare dir to work in Windows and Linux environments
        $dir = rtrim(__DIR__, '/');

        $this->loadViewsFrom($dir.'/../views', 'datagrid');
    }

    /**
     * Register service provider
     * @return void
     */
    public function register()
    {
        ///$this->app->bind();

        $this->app->singleton(
            'filter',
            function () {
                return $this->app->make('Apphp\DataGrid\Filter');
            }
        );

        $this->app->singleton(
            'pagination',
            function () {
                return $this->app->make('Apphp\DataGrid\Pagination');
            }
        );
    }
}
