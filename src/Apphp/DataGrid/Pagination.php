<?php

/**
 *  Helper for preparing pagination on query and pagination fields for rendering in view
 *
 *  Usage:
 *
 *  // Pagination
 *  $pagination = Pagination::init($query, 20, $sort, $direction, $filterFields)::paginate();
 *  $paginationFields = $pagination::getPaginationFields();
 *  $records = $pagination::getRecords();
 *
 *  // Render links
 *  {!! Apphp\DataGrid\Pagination::renderLinks() !!}
 *
 */

namespace Apphp\DataGrid;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Jenssegers\Agent\Agent;


class Pagination
{
    private static $_instance = null;

    /**
     * @var Request
     */
    private static $pageSize;
    private static $query;

    private static $sort = '';
    private static $direction = '';
    private static $filterFields = [];
    private static $paginationFields = [];
    private static $records = [];

    /**
     * Pagination constructor
     *
     * @param  null  $query
     *
     * @param  int  $pageSize
     * @param  string  $sort
     * @param  string  $direction
     * @param  string  $filterFields
     *
     * @return Pagination
     */
    public static function init($query, $pageSize = 20, $sort = '', $direction = '', $filterFields = '') : Pagination
    {
        if ( ! empty($query)) {
            self::setQuery($query);
        }
        if ( ! empty($pageSize)) {
            self::setPageSize($pageSize);
        }

        if (! empty($filterFields)) {
            self::$filterFields = $filterFields;
        }
        if (! empty($sort)) {
            self::$sort = $sort;
        }
        if (! empty($direction)) {
            self::$direction = $direction;
        }

        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Set query
     * @param string $query
     * @return void
     */
    public static function setQuery($query):void
    {
        self::$query = $query;
    }

    /**
     * Get query
     * @return string
     */
    public static function getQuery()
    {
        return self::$query;
    }

    /**
     * Set page size
     * @param  int  $pageSize
     * @return void
     */
    public static function setPageSize(int $pageSize): void
    {
        self::$pageSize = $pageSize;
    }

    /**
     * Get page size
     * @return void
     */
    public static function getPageSize(): int
    {
        return self::$pageSize;
    }

    /**
     * Get pagination fields
     * @return array
     */
    public static function getPaginationFields():array
    {
        return self::$paginationFields;
    }

    /**
     * Get records
     * @return LengthAwarePaginator
     */
    public static function getRecords(): LengthAwarePaginator
    {
        return self::$records;
    }

    /**
     * Handle pagination and prepare pagination fields
     *
     * @param  bool  $paginate
     *
     * @return null|Filter
     */
    public static function paginate($paginate = true): ?Pagination
    {
        if (empty(self::$_instance)){
            return null;
        }

        if ($paginate) {
            $records = self::$query->paginate(self::$pageSize);
        } else {
            $records = self::$query;
        }

        self::$records = $records;

        $maxRecordsOnPage = $records->currentPage() * $records->perPage();

        self::$paginationFields['fromRecord'] = $records->currentPage() > 1 ? ($records->currentPage() - 1) * $records->perPage() + 1 : 1;
        self::$paginationFields['toRecord'] = $records->total() < $maxRecordsOnPage ? $records->total() : $maxRecordsOnPage;

        return self::$_instance;
    }

    /**
     * Render pagination links
     * @return string
     */
    public static function renderLinks()
    {
        $links = self::$records->appends(array_merge(array_filter(self::$filterFields), ['sort' => self::$sort, 'direction' => self::$direction]));

        $agent = new Agent();
        if ($agent->isMobile()) {
            $links->onEachSide(1);
        }

        $output = '
        <div class="pt-1 d-flex flex-fill row">
            <div class="flex-fill col-12 col-lg-8">
                '.$links->links().'
            </div>
            <div class="flex-fill text-lg-right col-12 col-lg-4">
                Shows: '.self::$paginationFields['fromRecord'].' - '.self::$paginationFields['toRecord'].'
                from '.self::$records->total().'
            </div>
        </div>';

        return $output;
    }

}