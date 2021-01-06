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
        ///dd($columns);

        $output .= '<div class="table-responsive">';
        $output .= '<table class="table table-bordered table-striped">';

        // Render column names
        $output .= '<thead>';
        $output .= '<tr>';
        foreach ($columns as $key => $column) {
            $title = !empty($column['title']) ? $column['title'] : $column;
            $width = !empty($column['width']) ? ' width="'.$column['width'].'"' : '';
            $class = !empty($column['headClass']) ? ' class="'.$column['headClass'].'"' : '';

            $output .= '<th class=""'.$width.$class.'>'.$title.'</th>';
        }
        $output .= '</tr>';
        $output .= '</thead>';

        // Render rows
        $output .= '<tbody>';
        foreach (self::$records as $record) {
            $output .= '<tr>';
            foreach ($columns as $key => $column) {
                $columnKey = is_array($column) ? $key : $column;
                $callback = !empty($column['callback']) ? $callback : null;
                $class = !empty($column['class']) ? ' class="'.$column['class'].'"' : '';

                $output .= '<td'.$class.'>';
                if (!empty($column['callback'])) {
                    $output .= $column['callback']($record);
                } else {
                    $output .= $record[$columnKey] ?? '';
                }
                $output .= '</td>';
            }
            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';

        return $output;
    }

};