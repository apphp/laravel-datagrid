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
        $sortBy = 'user';
        $sortDirection = 'asc';
        $filterFields = [];

        $this->expectExceptionMessage('Wrong type of object: $query');
        Pagination::init(null, 20, $sortBy, $sortDirection, $filterFields);
    }

    /**
     * Test pagination initialization
     */
    public function testPaginationInit(): void
    {
        $sortBy = 'user';
        $sortDirection = 'asc';
        $filterFields = ['field1'=>'value1'];

        $tables = \Schema::getAllTables();
        $database = \Config::get('database.connections.mysql.database');
        $firstTable = ($tables[0]->{'Tables_in_' . $database});
        $query = \DB::table($firstTable);

        $pagination = Pagination::init($query, 20, $sortBy, $sortDirection, $filterFields);

        // Test $pagination type
        $this->assertTrue($pagination instanceof Pagination);

        // Test pagination
        $this->assertEquals($pagination::getPageSize(), 20);
        $pagination::setPageSize(10);
        $this->assertEquals($pagination::getPageSize(), 10);

        // Test sort by
        $this->assertEquals($pagination::getSortBy(), $sortBy);

        // Test sort direction
        $this->assertEquals($pagination::getSortDirection(), $sortDirection);

        // Test filter fields
        $this->assertEquals(array_diff($pagination::getFilterFields(), $filterFields), []);

        // Test query
        $this->assertEquals($pagination::getQuery(), $query);
        $pagination::setQuery($query);
        $this->assertEquals($pagination::getQuery(), $query);
    }

}
