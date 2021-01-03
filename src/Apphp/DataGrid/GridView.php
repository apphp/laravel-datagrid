<?php

/**
 *  Helper for drawing grid table of fields in view
 *
 *  Usage:
 *
 *  // GridView
 *  GridView::init($records);
 *
 *  // Render links
 *  {!! Apphp\DataGrid\GridView::renderTable() !!}
 *
 */

namespace Apphp\DataGrid;


class GridView
{

    private static $instance = null;

    private static $records = [];

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

}