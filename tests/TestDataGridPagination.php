<?php

namespace Apphp\DataGrid\Tests;

use Tests\TestCase;
use Apphp\DataGrid\Pagination;


class TestDataGridPagination extends TestCase
{

    /**
     * Test pagination empty
     */
    public function testPaginationEmpty(): void
    {
        $sort = 'user';
        $direction = 'asc';
        $filterFields = [];

        $this->expectExceptionMessage('Wrong type of object: $query');
        Pagination::init(null, 20, $sort, $direction, $filterFields);
    }

    /**
     * Test pagination initialization
     */
    public function testPaginationInit(): void
    {
        $sort = 'user';
        $direction = 'asc';
        $filterFields = [];

        $tables = \Schema::getAllTables();
        $database = \Config::get('database.connections.mysql.database');
        $firstTable = ($tables[0]->{'Tables_in_' . $database});
        $query = \DB::table($firstTable);

        $pagination = Pagination::init($query, 20, $sort, $direction, $filterFields);

        $this->assertEquals($pagination::getPageSize(), 20);
    }

}
