<?php

/**
 *  Helper for preparing pagination on query and pagination fields for rendering in view
 *
 *  Usage:
 *
 *  // Pagination
 *  $pagination = Pagination::init($query, 20, $sortBy, $sortDirection, $filterFields)::paginate();
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
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;


class Pagination
{
    private static $instance = null;

    /**
     * @var
     */
    private static $pageSize;
    private static $query;

    private static $sortBy = '';
    private static $sortDirection = '';
    private static $filterFields = [];
    private static $paginationFields = [];
    private static $records = [];

    /**
     * Pagination constructor
     *
     * @param  object $query
     * @param  int  $pageSize
     * @param  string|null  $sortBy
     * @param  string|null  $sortDirection
     * @param  array|null  $filterFields
     *
     * @return Pagination
     */
    public static function init($query, int $pageSize = 20, ?string $sortBy = null, ?string $sortDirection = '', ?array $filterFields = []): Pagination
    {
        static::guardIsRelationObject($query);

        if ( ! empty($query)) {
            self::setQuery($query);
        }
        if ( ! empty($pageSize)) {
            self::setPageSize($pageSize);
        }
        if ( ! empty($filterFields)) {
            self::$filterFields = $filterFields;
        }
        if ( ! empty($sortBy)) {
            self::$sortBy = $sortBy;
        }
        if ( ! empty($sortDirection)) {
            self::$sortDirection = $sortDirection;
        }

        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Set query
     *
     * @param  object $query
     * @return void
     */
    public static function setQuery($query = null): void
    {
        static::guardIsRelationObject($query);

        self::$query = $query;
    }

    /**
     * Get query
     *
     * @return string
     */
    public static function getQuery()
    {
        return self::$query;
    }

    /**
     * Set page size
     *
     * @param  int  $pageSize
     * @return void
     */
    public static function setPageSize(int $pageSize): void
    {
        self::$pageSize = $pageSize;
    }

    /**
     * Get page size
     *
     * @return int
     */
    public static function getPageSize(): int
    {
        return self::$pageSize;
    }

    /**
     * Get sort by
     *
     * @return string
     */
    public static function getSortBy(): string
    {
        return self::$sortBy;
    }

    /**
     * Get sort direction
     *
     * @return string
     */
    public static function getSortDirection(): string
    {
        return self::$sortDirection;
    }

    /**
     * Get pagination fields
     *
     * @return array
     */
    public static function getPaginationFields(): array
    {
        return self::$paginationFields;
    }

    /**
     * Get filter fields
     *
     * @return array
     */
    public static function getFilterFields(): array
    {
        return self::$filterFields;
    }

    /**
     * Get records
     *
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
        if (empty(self::$instance)) {
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
        self::$paginationFields['toRecord']   = $records->total() < $maxRecordsOnPage ? $records->total() : $maxRecordsOnPage;

        return self::$instance;
    }

    /**
     * Render pagination links
     *
     * @return string
     */
    public static function renderLinks()
    {
        $links = self::$records->appends(
            array_merge(array_filter(self::$filterFields), ['sort' => self::$sortBy, 'direction' => self::$sortDirection])
        );

        $agent = new Agent();
        if ($agent->isMobile()) {
            $links->onEachSide(1);
        }

        return view(
            'datagrid::pagination',
            [
                'links'            => $links,
                'paginationFields' => self::$paginationFields,
                'total'            => self::$records->total()
            ]
        );
    }

    /**
     * Guard is relation object
     * @param  null  $query
     */
    protected static function guardIsRelationObject($query = null)
    {
        if ( ! ($query instanceof Relation || $query instanceof Builder || $query instanceof QueryBuilder)) {
            throw new \InvalidArgumentException('Wrong type of object: $query');
        }
    }

}