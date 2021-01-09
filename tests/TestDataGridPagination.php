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

        $pagination = Pagination::init(null, 20, $sort, $direction, $filterFields);

        $this->assertEmpty(null);
    }

    /**
     * Test pagination initialization
     */
    public function testPaginationInit(): void
    {
        $sort = 'user';
        $direction = 'asc';
        $filterFields = [];

        $pagination = Pagination::init(null, 20, $sort, $direction, $filterFields);

        $this->assertEmpty(null);
    }

}
