<?php

namespace Apphp\DataGrid;

use Illuminate\Support\ServiceProvider;


class DataGridServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    private $dir = '';

    /**
     * DataGridServiceProvider constructor.
     *
     * @param $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        // Prepare dir to work in Windows and Linux environments
        $this->dir = rtrim(__DIR__, '/');
    }

    /**
     * Bootstrap the application events
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom($this->dir.'/../views', 'datagrid');
    }

    /**
     * Register service provider
     * @return void
     */
    public function register()
    {
        $this->app->singleton('filter', function () {
            return $this->app->make('Apphp\DataGrid\Filter');
        });

        $this->app->singleton('pagination', function () {
            return $this->app->make('Apphp\DataGrid\Pagination');
        });

        $this->app->singleton('message', function () {
            return $this->app->make('Apphp\DataGrid\Message');
        });
    }
}
