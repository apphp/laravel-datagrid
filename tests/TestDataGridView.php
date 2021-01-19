<?php

namespace Apphp\DataGrid\Tests;

use Tests\TestCase;
use Apphp\DataGrid\GridView;


class TestDataGridView extends TestCase
{

    /**
     * Test datagrid view
     */
    public function testGridViewEmpty(): void
    {
        GridView::init([]);
        $this->assertEquals(GridView::getRecords(), []);
    }

    /**
     * Test datagrid view
     */
    public function testGridViewNotEmpty(): void
    {
        $params = ['a' => 1, 'b' => 2];
        GridView::init($params);
        $this->assertSame(GridView::getRecords(), $params);
    }

}
