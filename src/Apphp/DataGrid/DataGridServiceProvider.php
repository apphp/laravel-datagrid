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
     * Register service provider
     *
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

    /**
     * Bootstrap the application events
     *
     * @return void
     */
    public function boot()
    {

    }
}
