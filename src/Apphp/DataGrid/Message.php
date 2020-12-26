<?php

/**
 *  Helper for rendering messages
 *
 *  Usage:
 *
 *  {!! \Apphp\DataGrid\Message::warning('Sorry, no records were found. Please adjust your search criteria and try again.') !!}
 *
 */

namespace Apphp\DataGrid;


class Message
{
    /**
     * Return message
     *
     * @param  string  $message
     * @param  bool  $important
     * @return string
     */
    public static function warning(string $message = '', bool $important = true)
    {
        return '<div class="alert alert-warning'.($important ? ' alert-important' : '').'">'.$message.'</div>';
    }

}