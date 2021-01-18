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

        // Test is filtered
        $this->assertFalse($filter->isFiltered());
        $filtersFilled['act'] = 'search';
        $filter->setFilters($filtersFilled);
        $filter->setFilterFields(array_fill_keys(array_keys($filtersFilled), ''));
        $this->assertTrue($filter->isFiltered());

        // Test submit routing
        $this->assertEquals($submitRoute, $filter->getSubmitRoute());
        $submitRoute = 'my-submit-route';
        $filter->setSubmitRoute($submitRoute);
        $this->assertEquals($submitRoute, $filter->getSubmitRoute());

        // Test cancel routing
        $this->assertEquals($cancelRoute, $filter->getCancelRoute());
        $cancelRoute = 'my-submit-route';
        $filter->setCancelRoute($cancelRoute);
        $this->assertEquals($cancelRoute, $filter->getCancelRoute());

        // Test fields count in row
        $this->assertEquals($filter->getFieldsInRow(), 4);
        $filter->setFieldsInRow(6);
        $this->assertEquals($filter->getFieldsInRow(), 6);
        $filter->setFieldsInRow(5);
        $this->assertEquals($filter->getFieldsInRow(), 4);
        $filter->setFieldsInRow(-1);
        $this->assertEquals($filter->getFieldsInRow(), 4);

        // Test mode
        $this->assertEquals($filter->getMode(), 'opened');
        $filter->setMode('collapsed');
        $this->assertEquals($filter->getMode(), 'collapsed');
        $filter->setMode('opened');
        $this->assertEquals($filter->getMode(), 'opened');
        $filter->setMode('no');
        $this->assertEquals($filter->getMode(), 'opened');

        // Test errors
        $this->assertEquals($filter->renderErrors(), '');

        // Test js rendering
        $this->assertStringStartsWith('window.addEventListener', $filter->renderJs());

        // Test fields rendering
        $this->assertStringStartsWith('<div id="filter', $filter->renderFields());
    }

}
