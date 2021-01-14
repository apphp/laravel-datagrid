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
        $fieldsInRow = '';

        $this->expectExceptionMessage('Wrong type of object: $query');
        Filter::init(null, $request, $filters, $submitRoute, $cancelRoute, $initMode, $fieldsInRow);
    }

    /**
     * Test filter arguments
     */
    public function testFilterArguments(): void
    {
        $request = request();
        $filters = [];
        $submitRoute = '';
        $cancelRoute = '';
        $initMode = '';
        $fieldsInRow = '';
        $query = null;

        $filter = Filter::init($query, $request, $filters, $submitRoute, $cancelRoute, $initMode, $fieldsInRow);

        // Test $filter type
        $this->assertTrue($filter instanceof Filter);

        // Test query
        $this->assertEquals($filter::getQuery(), null);
        $query = new stdClass();
        $filter::setQuery($query);
        $this->assertTrue($query instanceof stdClass);

        // Test filter fields
        $filters = ['filed1' => ['type' => 'int'], 'filed2' => ['type' => 'string']];

        $this->assertEquals($filters, $filter->getFilters());
        $filter->setFilters($filters);
        $this->assertEquals($filters, $filter->getFilters());

        $this->assertEquals($filters, $filter->getFilterFields());
        $filter->setFilterFields(array_fill_keys(array_keys($filters), ''));
        $this->assertEquals($filters, $filter->getFilterFields());

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
