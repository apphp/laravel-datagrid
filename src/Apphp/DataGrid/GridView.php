<?php

/**
 *  Helper for drawing grid table of fields in view
 *
 *  Usage:
 *
 *  // GridView
 *  $gridView = GridView::init($records);
 *
 *  // Render table with auto generated columns
 *  {!! $gridView::renderTable() !!}
 *
 *  // Render table with predefined columns
 *  {!! $gridView::renderTable([
 *      'user_id'           => ['title' => 'ID', 'width'=>'60px', 'headClass'=>'text-right', 'class'=>'text-right', 'sortable'=>true],
 *      'username'          => ['title' => 'Username', 'width'=>'', 'headClass'=>'text-left', 'class'=>'', 'sortable'=>true, 'callback'=>function($user){ return '<img src="'.$user->avatar_path.'" class="w-30px mr-2" alt="" /> <a href="'.route('backend.users.show', $user).'" title="Click to edit">'.$user->username.'</a>'; }],
 *      'name'              => ['title' => 'Name', 'width'=>'', 'headClass'=>'text-left', 'class'=>'', 'sortable'=>true],
 *      'email'             => ['title' => 'Email', 'width'=>'', 'headClass'=>'text-left', 'class'=>'text-truncate px-2', 'sortable'=>true],
 *      'created_at'        => ['title' => 'Created At', 'width'=>'160px', 'headClass'=>'text-center', 'class'=>'text-center px-1', 'sortable'=>true],
 *      'last_login_at'     => ['title' => 'Last Login', 'width'=>'160px', 'headClass'=>'text-center', 'class'=>'text-center px-1', 'sortable'=>true],
 *      'newsletter'        => ['title' => '<i class="fa fa-envelope-o" aria-hidden="true" title="Subscribed to newsletter"></i>', 'width'=>'40px', 'headClass'=>'text-center', 'class'=>'text-center px-1', 'sortable'=>true],
 *      'email_verified_at' => ['title' => 'Status', 'width'=>'80px', 'headClass'=>'text-center', 'class'=>'text-center px-2', 'sortable'=>false, 'callback'=>function($user){ return $user->isVerified() ? '<span class="badge badge-primary">Verified</span>' : '<span class="badge badge-secondary">Waiting</span>'; }],
 *      'active'            => ['title' => 'Active', 'width'=>'80px', 'headClass'=>'text-center', 'class'=>'text-center px-2', 'sortable'=>false, 'callback'=>function($user){ return $user->isActive() ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Not Active</span>'; }],
 *  ]) !!}
 *
 */

namespace Apphp\DataGrid;


class GridView
{

    /* @var GridView */
    private static $instance = null;

    /* @var array */
    private static $records = [];

    /* @var bool */
    private static $sortingEnabled = true;

    /* @var bool */
    private static $responsiveEnabled = true;

    /**
     * GridView constructor
     *
     * @param  array  $records
     * @return GridView
     */
    public static function init($records = []): GridView
    {
        self::$records = $records;

        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get records
     * @return array
     */
    public static function getRecords()
    {
        return self::$records;
    }

    /**
     * Render table
     *
     * @param  null  $columns
     * @return string
     */
    public static function renderTable($columns = null)
    {
        $output = '';

        if ( !count(self::$records)) {
            return $output;
        }

        if ( !empty($columns) && !is_array($columns) && !is_string($columns) ) {
            return $output;
        }

        // Prepare columns, if no columns specified
        if (empty($columns)) {
            if ($record = self::$records->first()) {
                $columns = array_merge([$record->getKeyName()], array_keys($record->attributesToArray()));
                $columns = array_combine($columns, $columns);
            }
        }

        return view(
            'datagrid::gridview',
            [
                'headers'    => self::renderTableHeaders($columns),
                'rows'       => self::renderTableRows($columns),
                'responsive' => self::$responsiveEnabled,
            ]
        );

        return $output;
    }

    /**
     * Render table column headers
     *
     * @param  array  $columns
     * @return string
     */
    private static function renderTableHeaders(array $columns = []): string
    {
        $currentURL = request()->fullUrl();

        $sort      = request()->get('sort');
        $direction = strtolower(request()->get('direction'));

        $currentURL = self::removeParameterFromUrl($currentURL, 'sort');
        $currentURL = self::removeParameterFromUrl($currentURL, 'direction');

        return view(
            'datagrid::gridview-headers',
            [
                'currentURL' => $currentURL,
                'sort' => $sort,
                'direction' => $direction,
                'sortingEnabled' => self::$sortingEnabled,
                'columns' => $columns,
            ]
        );
    }

    /**
     * Render table rows
     *
     * @param  array  $columns
     * @return string
     */
    private static function renderTableRows(array $columns = []): string
    {
        return view(
            'datagrid::gridview-rows',
            [
                'records' => self::$records,
                'columns' => $columns
            ]
        );
    }

    /**
     * Remove specific parameter from Url
     * @param  string  $url
     * @param  string  $key
     * @return string
     */
    private static function removeParameterFromUrl(string $url = '', string $key = ''): string
    {
        $parsed = parse_url($url);
        $path   = $parsed['path'];

        unset($_GET[$key]);

        if ( ! empty(http_build_query($_GET))) {
            $return = $path.'?'.http_build_query($_GET);
        } else {
            $return = $path;
        }

        return $return;
    }
}
