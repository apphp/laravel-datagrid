<?php

/**
 *  Helper for preparing filters on query and filter fields for rendering in view
 *
 *  Usage:
 *
 *    // Source for dropdown boxes
 *    $array = ['key' => value];
 *
 *    // If defined, then search in relation tables: see relation method name
 *    'relation' => 'relationName'
 *    'relation' => ['relationName1', 'relationName2']
 *    'relationXref' => 'relationXrefTableName' - used for relations when we use connection table, like "ticket_user_xref"
 *
 *    // Each option may be disabled by
 *    'disabled' => true
 *
 *    // Specify html options for each field
 *    'htmlOptions' => ['option1'=>'value1', 'option2'=>'value2']
 *
 *    // Define filters and filter field types
 *    $filters = [
 *      'act'      => ['type' => 'equals',  'value' => 'search'],
 *      'param'    => ['type' => 'url_parameter', 'value' => 'search'],
 *
 *      'field_1'  => ['title' => 'String',     'type' => 'string',       'compareType' => '%like%',    'validation'=>['minLength'=>2, 'maxLength'=>10],    'relation'=>'relationName',    'relationXref'=>''],
 *      'field_2'  => ['title' => 'Integer',    'type' => 'integer|int',  'compareType' => '=',         'validation'=>['min'=>2, 'max'=>100]],
 *      'field_3'  => ['title' => 'Set',        'type' => 'set',          'compareType' => '=',         'source' => $array],
 *      'field_4'  => ['title' => 'Date',       'type' => 'date',         'compareType' => 'like%'],
 *      'role'     => ['title' => 'Role',       'type' => 'user_role',    'compareType' => '',          'source' => $array],
 *      'status'   => ['title' => 'Status',     'type' => 'user_status',  'compareType' => '',          'source' => $array],
 *      'active'   => ['title' => 'Active',     'type' => 'user_active',  'compareType' => '',          'source' => $array],
 *    ];
 *    $filterFields = array_fill_keys(array_keys($filters), '');
 *
 *    -- Controller  --
 *    // Handle filters and prepare SQL query
 *    $filter = Filter::init($query, $request, $filters, $filterFields, route('backend.submit'), route('backend.cancel'), 'collapsed');
 *    $filter::setMode('opened|collapsed');
 *    $filter::filter();
 *    $filterFields = $filter::getFilterFields();
 *    $query = $filter::getQuery();
 *
 *    -- View Files --
 *    <script>
 *        {!! Apphp\DataGrid\\Filter::renderJs() !!}
 *    </script>
 *
 *    {!! Apphp\DataGrid\Filter::renderFields() !!}
 */

namespace Apphp\DataGrid;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;


class Filter
{
    private static $instance = null;

    /**
     * @var Request
     */
    private static $request = null;
    private static $filters = [];
    private static $filterFields = [];
    private static $query = '';
    private static $submitRoute = '';
    private static $cancelRoute = '';
    private static $fieldsInRow = 4;
    private static $mode = 'opened';        /* collapsed or opened */

    private static $debug = false;
    private static $errors = [];


    /**
     * Filter constructor
     *
     * @param  Illuminate\Database\Query\Builder  $query
     * @param  Request  $request
     * @param  array|null  $filters
     * @param  string  $submitRoute
     * @param  string  $cancelRoute
     * @param  string  $initMode
     * @param  int  $fieldsInRow
     * @return Filter
     */
    public static function init(
        $query = null,
        Request $request,
        array $filters = null,
        string $submitRoute = '',
        string $cancelRoute = '',
        string $initMode = '',
        int $fieldsInRow = 4
    ): Filter
    {
        self::$debug = true;
        self::$request = $request;

        static::guardIsEmptyQuery($query);
        static::guardIsRelationObject($query);

        if ( ! empty($query)) {
            self::setQuery($query);
        }
        if ( ! empty($filters)) {
            self::setFilters($filters);
            self::setFilterFields(array_fill_keys(array_keys($filters), ''));
        }
        if ( ! empty($submitRoute)) {
            self::setSubmitRoute($submitRoute);
        }
        if ( ! empty($cancelRoute)) {
            self::setCancelRoute($cancelRoute);
        }
        if ( ! empty($fieldsInRow)) {
            self::setFieldsInRow($fieldsInRow);
        }

        $agent = new Agent();
        if ($agent->isMobile()) {
            self::setMode('collapsed');
        } elseif ( ! empty($initMode)) {
            self::setMode($initMode);
        }

        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Returns is data was filtered
     * @return bool
     */
    public static function isFiltered(): bool
    {
        return !empty(self::$filters['act']) && self::$filters['act'] === 'search';
    }

    /**
     * Set filters
     * @param array|null $filters
     * @return void
     */
    public static function setFilters(?array $filters = []): void
    {
        self::$filters = $filters;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public static function getFilters(): array
    {
        return self::$filters;
    }

    /**
     * Set filter fields
     * @param $filterFields
     * @return void
     */
    public static function setFilterFields($filterFields): void
    {
        self::$filterFields = $filterFields;
    }

    /**
     * Get filter fields
     * @return array
     */
    public static function getFilterFields(): array
    {
        return self::$filterFields;
    }

    /**
     * Set submit route
     * @param string $route
     * @return void
     */
    public static function setSubmitRoute($route = ''): void
    {
        self::$submitRoute = $route;
    }

    /**
     * Get submit route
     * @return string
     */
    public static function getSubmitRoute(): string
    {
        return self::$submitRoute;
    }

    /**
     * Set cancel route
     * @param string $route
     * @return void
     */
    public static function setCancelRoute($route = ''): void
    {
        self::$cancelRoute = $route;
    }

    /**
     * Get cancel route
     * @return string
     */
    public static function getCancelRoute(): string
    {
        return self::$cancelRoute;
    }

    /**
     * Set fields in row
     * @param  int  $count
     * @return void
     */
    public static function setFieldsInRow(int $count = 4): void
    {
        self::$fieldsInRow = in_array($count, [4, 6]) ? $count : 4;
    }

    /**
     * Get fields in row
     * @return int
     */
    public static function getFieldsInRow(): int
    {
        return self::$fieldsInRow;
    }

    /**
     * Set mode
     * @param string $mode
     * @return void
     */
    public static function setMode($mode): void
    {
        self::$mode = (strtolower($mode) === 'collapsed' ? 'collapsed' : 'opened');
    }

    /**
     * Get mode
     * @return string
     */
    public static function getMode(): string
    {
        return self::$mode;
    }

    /**
     * Set query
     * @param string $query
     * @return void
     */
    public static function setQuery($query): void
    {
        static::guardIsEmptyQuery($query);
        static::guardIsRelationObject($query);

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
     * Handle filters and prepare SQL query
     *
     * @return null|Filter
     */
    public static function filter(): ?Filter
    {
        if (empty(self::$instance)){
            return null;
        }

        foreach (self::$filters as $key => $filter) {

            // Get field value according to specified key or form field name
            $value = self::$request->get($key);
            if (!empty($filter['htmlOptions']['name'])) {
                $value = self::$request->get($filter['htmlOptions']['name']);
            }

            $defaultValue = $filter['value'] ?? '';
            $fieldTitle = $filter['title'] ?? $key;
            $source = !empty($filter['source']) ? array_keys($filter['source']) : [];
            $validation = !empty($filter['validation']) && is_array($filter['validation']) ? $filter['validation'] : [];
            $minLength = $validation['minLength'] ?? null;
            $maxLength = $validation['maxLength'] ?? null;
            $min = $validation['min'] ?? null;
            $max = $validation['max'] ?? null;

            // Don't show disabled filter options
            if (!empty($filter['disabled'])) {
                continue;
            }

            if ($value != '') {
                switch ($filter['type']) {
                    case 'int':
                    case 'integer':
                        if (is_numeric($value)) {
                            // Min/Max value validation
                            if ( ! empty($min) && $value < $min) {
                                self::$errors[] = 'The value of field <b>'.$fieldTitle.'</b> must be greater than or equal to '.number_format($min).'.';
                            }
                            if ( ! empty($max) && $value > $max) {
                                self::$errors[] = 'The value of field <b>'.$fieldTitle.'</b> must be less than or equal to '.number_format($max).'.';
                            }

                            if (empty(self::$errors)) {
                                self::$filterFields[$key] = $value;
                                self::prepareWhereClause($key, $value, $filter);
                            }
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> must be a numeric value.';
                        }
                        break;
                    case 'equals':
                        if (is_string($value) && $value == $defaultValue) {
                            self::$filterFields[$key] = $value;
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> must be equal to '.$defaultValue.'.';
                        }
                        break;
                    case 'set':
                        if (is_array($source) && in_array($value, $source)) {
                            self::$filterFields[$key] = $value;
                            self::prepareWhereClause($key, $value, $filter);
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> must be one of a pre-defined values of set.';
                        }
                        break;
                    case 'date':
                        if (is_string($value) && strlen($value) === 10) {
                            self::$filterFields[$key] = $value;
                            self::prepareWhereClause($key, $value, $filter);
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> must be a date value.';
                        }
                        break;
                    case 'url_parameter':
                        if (is_string($value) && $value == $defaultValue) {
                            self::$filterFields[$key] = $value;
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> must be equal to '.$defaultValue.'.';
                        }
                        break;
                    case 'user_role':
                        if ((is_string($value) && strlen($value) < 100) || (is_array($value) && count($value) < 500)) {
                            self::$filterFields[$key] = $value;

                            if (is_string($value)) {
                                $value = array_filter(explode(',', $value));
                            }

                            $whereHasClause = 'whereHas';
                            $query = self::$query;

                            if (in_array('user', $value)) {
                                $query->whereDoesntHave('roles');
                                $whereHasClause = 'orWhereHas';
                                unset($value[array_search('user', $value)]);
                            }

                            if (count($value)) {
                                $query->$whereHasClause('roles', function ($query) use ($value) {
                                    $count = 0;
                                    foreach ($value as $val) {
                                        if ($val == 'user') continue;
                                        if (!$count) {
                                            $query->where('name', '=', $val);
                                        }else{
                                            $query->orWhere('name', '=', $val);
                                        }
                                        $count++;
                                    }
                                });
                            }
                            self::$query = $query;
                        }
                        break;
                    case 'user_status':
                        if (is_string($value)) {
                            self::$filterFields[$key] = $value;
                            if ($value == 'verified') {
                                self::$query->whereNotNull('email_verified_at');
                            } else {
                                self::$query->whereNull('email_verified_at');
                            }
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> has undefined value.';
                        }
                        break;
                    case 'user_active':
                        if (is_string($value)) {
                            self::$filterFields[$key] = $value;
                            self::$query->where('active', '=', (self::$request->get('active') == 'active' ? 1 : 0));
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> has undefined value.';
                        }
                        break;
                    default:
                    case 'string':
                        if (is_string($value)) {
                            // Min/Max length validation
                            if ( ! empty($minLength) && strlen($value) < $minLength) {
                                self::$errors[] = 'The length of field <b>'.$fieldTitle.'</b> must be at least '.number_format($minLength).' characters.';
                            }
                            if ( ! empty($maxLength) && strlen($value) > $maxLength) {
                                self::$errors[] = 'The length of field <b>'.$fieldTitle.'</b> must be at less than or equal to '.number_format($maxLength).' characters.';
                            }

                            if (empty(self::$errors)) {
                                self::$filterFields[$key] = $value;
                                self::prepareWhereClause($key, $value, $filter);
                            }
                        } elseif (self::$debug){
                            self::$errors[] = 'The field <b>'.$fieldTitle.'</b> must be a string value.';
                        }
                        break;
                }
            }
        }

        return self::$instance;
    }

    /**
     * Prepare where clause for SQL
     * @param string $key
     * @param mixed $value
     * @param array $filter
     * @return void
     */
    private static function prepareWhereClause(string $key, $value, array $filter = []): void
    {
        $compareType = $filter['compareType'] ?? '=';
        $relation = $filter['relation'] ?? null;
        $relationXref = !empty($filter['relationXref']) ? $filter['relationXref'].'.' : '';

        // Add relation Xref table name, if needed
        $key = $relationXref.$key;

        if (!empty($relation)) {
            // Search in relation
            if (is_array($relation)) {
                foreach ($relation as $rel) {
                    self::$query->orWhereHas($rel, function($query) use($key, $value, $compareType){
                        self::setWhereClause($key, $value, $compareType, $query);
                    })->get();
                }
            }else{
                self::$query->whereHas($relation, function($query) use($key, $value, $compareType){
                    self::setWhereClause($key, $value, $compareType, $query);
                })->get();
            }
        } else {
            // Search in the main table
            self::setWhereClause($key, $value, $compareType);
        }
    }

    /**
     * Set where clause for SQL
     * @param string $key
     * @param mixed $value
     * @param string $compareType
     * @param null|object $query
     * @return void
     */
    private static function setWhereClause(string $key, $value, string $compareType, array $query = null): void
    {
        $q = empty($query) ? self::$query : $query;

        switch ($compareType) {
            case '%like%':
                $q->where($key, 'like', '%' . $value . '%');
                break;
            case 'like%':
                $q->where($key, 'like', $value . '%');
                break;
            case '%like':
                $q->where($key, 'like', '%' . $value);
                break;
            case 'like':
                $q->where($key, 'like', $value);
                break;
            default:
                $q->where($key, $value);
                break;
        }
    }

    /**
     * Render errors
     * @return string
     */
    public static function renderErrors()
    {
        $output = '';

        if (self::$debug && !empty(self::$errors)) {
            $output .= '<div class="alert alert-danger alert-important" role="alert">';
            $output .= '<button type="button" data-dismiss="alert" aria-hidden="true" class="close">×</button>';
            $output .= '<b>Filter Errors:</b>';
            $output .= '<ul class="mb-0">';
            foreach (self::$errors as $error) {
                $output .= '<li>' . $error . '</li>';
            }
            $output .= '</ul>';
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Render JS script
     * @return string
     */
    public static function renderJs()
    {
        $output = 'window.addEventListener(
            "resize",
            function () {
                if (window.innerWidth < 768) {
                    if ( ! $("#filter").find(".card-body").hasClass("collapse")) {
                        $(".card-header a").click();
                    }
                }
            }
        );';

        return $output;
    }

    /**
     * Render filter fields
     * @return string
     */
    public static function renderFields()
    {
        $filterFields = self::$filterFields;
        $filters = self::$filters;

        $errors = self::renderErrors();

        $count = 0;
        $filterFieldsContent = '<div class="row mb-n2">'.PHP_EOL;
        foreach ($filters as $key => $filter) {
            // Skip act field
            if ($key === 'act') {
                continue;
            }

            // Don't show disabled filter options
            if (!empty($filter['disabled'])) {
                continue;
            }

            // Specify html options
            $htmlOptions = ['id' => $key, 'name' => $key, 'class' => 'form-control form-control-sm'];
            if (! empty($filter['htmlOptions'])) {
                $htmlOptions = array_merge($htmlOptions, $filter['htmlOptions']);
            }

            // Split rows in filter after each 4 fields
            if ($count != 0 && $count % self::$fieldsInRow == 0) {
                $filterFieldsContent .= '</div>'.PHP_EOL;
                $filterFieldsContent .= '<div class="row mb-n2">'.PHP_EOL;
            }

            $title = $filter['title'] ?? $key;
            $type = $filter['type'] ?? 'string';
            $source = $filter['source'] ?? [];
            $value = isset($filterFields[$key]) ? $filterFields[$key] : '';

            $filterFieldsContent .= '<div class="col-md-'.(self::$fieldsInRow == 6 ? 2 : 3).'">'.PHP_EOL;
            $filterFieldsContent .= '<div class="form-group">'.PHP_EOL;
            $filterFieldsContent .= '<label for="'.$key.'" class="col-form-label">'.$title.'</label>'.PHP_EOL;

            switch ($type) {
                case 'int':
                case 'integer':
                    $filterFieldsContent .= '<input type="number" min="1" id="'.$htmlOptions['id'].'" name="'.$htmlOptions['name'].'" class="'.$htmlOptions['class'].'" value="'.$value.'">'.PHP_EOL;
                    break;

                case 'date':
                    $filterFieldsContent .= '<input type="date" id="'.$htmlOptions['id'].'" name="'.$htmlOptions['name'].'" class="'.$htmlOptions['class'].'" value="'.$value.'">'.PHP_EOL;
                    break;

                case 'set':
                case 'user_status':
                case 'user_active':
                    $filterFieldsContent .= '<select id="'.$htmlOptions['id'].'" name="'.$htmlOptions['name'].'" class="'.$htmlOptions['class'].'">'.PHP_EOL;
                    $filterFieldsContent .= '<option value=""></option>'.PHP_EOL;
                    foreach ($source as $sourceValue => $sourceLabel) {
                        $filterFieldsContent .= '<option value="'.$sourceValue.'"'.($value != '' && $sourceValue == $value ? ' selected' : '').'>'.$sourceLabel.'</option>'.PHP_EOL;
                    }
                    $filterFieldsContent .= '</select>'.PHP_EOL;
                    break;

                case 'url_parameter':
                    $filterFieldsContent .= '<input type="hidden" name="'.$htmlOptions['name'].'" value="'.$value.'">'.PHP_EOL;
                    break;

                case 'user_role':
                    $rolesList = [];
                    foreach ($source as $vVal => $vLabel) {
                        $rolesList[] = ['value' => $vVal, 'label' => $vLabel];
                    }

                    // Selected roles for milti-select
                    $role = [];
                    if ( ! empty($value)) {
                        if (is_string($value)) {
                            $value = explode(',', $value);
                        }
                        $values = array_filter($value);
                        foreach ($values as $vLabel) {
                            if (isset($source[$vLabel])) {
                                $role[] = ['value' => $vLabel, 'label' => $source[$vLabel]];
                            }
                        }
                    }

                    if (config('datagrid.vueMultiselect')) {
                        $filterFieldsContent .= '<vue-multiselect :id="\''.$htmlOptions['id'].'\'" :options=\''.json_encode($rolesList).'\' :values=\''.json_encode($role).'\' :placeholder-text="\'Select Role\'"></vue-multiselect>'.PHP_EOL;
                    } else {
                        $roleValues = array_column($role, 'value');
                        $filterFieldsContent .= '<select id="'.$htmlOptions['id'].'" name="'.$htmlOptions['id'].'[]" class="form-control form-control-sm" multiple>';
                        foreach ($rolesList as $roleItem) {
                            $selected = in_array($roleItem['value'], $roleValues) ? ' selected' : '';
                            $filterFieldsContent .= '<option value="'.$roleItem['value'].'"'.$selected.'>'.$roleItem['label'].'</option>';
                        }
                        $filterFieldsContent .= '</select>';
                    }
                    break;

                case 'string':
                default:
                    $filterFieldsContent .= '<input type="text" maxlength="255" id="'.$htmlOptions['id'].'" name="'.$htmlOptions['name'].'" class="'.$htmlOptions['class'].'" value="'.$value.'" autocomplete="off" />'.PHP_EOL;
                    break;
            }

            $filterFieldsContent .= '</div>'.PHP_EOL;
            $filterFieldsContent .= '</div>'.PHP_EOL;

            $count++;
        }
        $filterFieldsContent .= '</div>'.PHP_EOL;

        return view(
            'datagrid::filter',
            [
                'mode'                => self::$mode,
                'errors'              => $errors,
                'filterFields'        => $filterFields,
                'filterFieldsContent' => $filterFieldsContent,
                'submitRoute'         => self::getSubmitRoute(),
                'cancelRoute'         => self::getCancelRoute(),
            ]
        );
    }

    /**
     * Guard is empty query
     * @param  null  $query
     */
    protected static function guardIsEmptyQuery($query = null)
    {
        if (empty($query)) {
            throw new \InvalidArgumentException('Missing or empty parameter : $query');
        }
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