<?php

namespace Apphp\DataGrid\Tests;

use stdClass;
use Tests\TestCase;
use Apphp\DataGrid\Filter;


class TestDataGridFilter extends TestCase
{

    /**
     * Test filter is empty
     */
    public function testFilterEmpty(): void
    {
        $request = request();
        $filters = [];
        $submitRoute = '';
        $cancelRoute = '';
        $initMode = '';
        $fieldsInRow = 4;

        $this->expectExceptionMessage('Missing or empty parameter : $query');
        Filter::init(null, $request, $filters, $submitRoute, $cancelRoute, $initMode, $fieldsInRow);
    }

    /**
     * Test filter arguments
     */
    public function testFilterArguments(): void
    {
        $request = request();
        $filtersInit = [];
        $filtersFilled = ['filed1' => ['type' => 'int'], 'filed2' => ['type' => 'string']];
        $submitRoute = '';
        $cancelRoute = '';
        $initMode = '';
        $fieldsInRow = 4;

        $tables = \Schema::getAllTables();
        $database = \Config::get('database.connections.mysql.database');
        $firstTable = ($tables[0]->{'Tables_in_' . $database});
        $query = \DB::table($firstTable);

        $filter = Filter::init($query, $request, $filtersInit, $submitRoute, $cancelRoute, $initMode, $fieldsInRow);

        // Test $filter type
        $this->assertTrue($filter instanceof Filter);

        // Test query
        $this->assertSame($filter::getQuery(), $query);

        // Test filters
        $this->assertSame($filtersInit, $filter->getFilters());
        $filter->setFilters($filtersFilled);
        $this->assertSame($filtersFilled, $filter->getFilters());

        // Test filter fields
        $this->assertSame(array_fill_keys(array_keys($filtersInit), ''), $filter->getFilterFields());
        $filter->setFilterFields(array_fill_keys(array_keys($filtersFilled), ''));
        $this->assertSame(array_fill_keys(array_keys($filtersFilled), ''), $filter->getFilterFields());

        // /isFiltered()

        // setSubmitRoute($route = '')
        // getSubmitRoute()

        // setCancelRoute
        // getCancelRoute

        // setFieldsInRow

        // setMode

        // filter() !!!!!!!!!

        // renderErrors()

        // renderJs()

        // renderFields()
    }

}
