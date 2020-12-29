[![License: MIT](https://img.shields.io/badge/License-MIT-brightgreen.svg)](https://opensource.org/licenses/MIT)


# DataGrid helpers for Laravel Framework Applications

This package helps to create DataGrid (CRUD) pages for Laravel 6+ framework applications.


## Requirements

* PHP >=7.0
* Laravel 6+
* Bootstrap 3+


## License

This project is released under the MIT License.   
Copyright © 2020 [ApPHP](https://www.apphp.com/).


## Installation

Begin by pulling in the package through Composer.

```bash
composer require apphp/laravel-datagrid
```

Next, make sure you connected Bootstrap. You may either pull in the Bootstrap's CSS within your HTML or layout file, or write your own CSS 
classes based on them. 

```html
<link rel="stylesheet" href="//getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css">
```

## Usage in Controllers  

### 1. Import classes
```php 
use Apphp\DataGrid\Pagination;
use Apphp\DataGrid\Filter;
```

### 2. Define filters and filter field types
```php
$filters      = [
    'act' => ['type' => 'equals', 'value' => 'search'],
    'email'    => ['title' => 'Email', 'type' => 'string', 'compareType' => '%like%', 'validation' => ['maxLength' => 150]],
    'name'     => ['title' => 'Name', 'type' => 'string', 'compareType' => '%like%'],
    'username' => ['title' => 'Username', 'type' => 'string', 'compareType' => '%like%'],
    'user_id'  => ['title' => 'ID', 'type' => 'integer', 'compareType' => '=', 'validation' => ['max' => 10000000]],
];
```

Following filter field types are available

| Type                | Description                                                                |
|---------------------|----------------------------------------------------------------------------|
| `string`            | Any type of strings         
| `integer` or `int`  | Numeric integer field (HTML type="number" attribute is used)         
| `set`               | Set of values (array)         
| `date`              | The datetime fields


Each filter field can include following attributes:

| Attribute        | Description                                                                   |
|------------------|-------------------------------------------------------------------------------|
| `title`          | Specifies a title, that will be shown in the label of filter field         
| `type`           | Specifies a type of the filter field (see above)         
| `compareType`    | Specifies which type of comparison will be used: ex.: '=', '%like%', '!=' etc.         
| `source`         | Specifies the source (array) to 'set' fields         
| `validation`     | Specifies validation rules (array). Possible options: ['minLength'=>2, 'maxLength'=>10, 'min'=>2, 'max'=>100]           
| `relation`       |          
| `relationXref`   |          
| `htmlOptions`    |          
| `disabled`       |            
  

### 3. Handle filters and prepare SQL builder
```php
// $query = User::sortable()->orderByDesc('id');
$query = User::orderByDesc('id');
$request = request(); // or get it via function param, like foo(Request $request){...}
$url = route('backend.users.submitRote');
$cancelUrl = $url;

$filter       = Filter::init($query, $request, $filters, $url, $cancelUrl, 'collapsed');
$filter       = $filter::filter();
$filterFields = $filter::getFilterFields();
$query        = $filter::getQuery();
```

### 4. Sorting
```php 
$sort      = $request->get('sort');
$direction = $request->get('direction');
```

### 5. Pagination
```php 
$pagination       = Pagination::init($query, 20, $sort, $direction, $filterFields)::paginate();
$paginationFields = $pagination::getPaginationFields();
$users            = $pagination::getRecords();
```

### 6. Rendering view
```php
return view('backend.users.mainView', compact('users', 'filterFields', 'paginationFields'));
```

## Usage in View files 
```html
<script>
    {!! \Apphp\DataGrid\Filter::renderJs() !!}
</script>

{!! \Apphp\DataGrid\Filter::renderFields() !!}
    
    <!-- YOUR TABLE WITH RECORDS DATA -->
        @foreach ($users as $user)
    
        @endforeach
    <!-- YOUR TABLE WITH RECORDS DATA -->

{!! \Apphp\DataGrid\Pagination::renderLinks() !!}
```

## Examples

### Controller code (full example)
```php
public function index(Request $request)
{
    // Additional data
    $roles    = Role::rolesList();
    $statuses = User::statusesList();
    $actives  = [0 => 'Not Active', 1 => 'Active'];

    // Define filters and filter field types
    $filters      = [
        'act' => ['type' => 'equals', 'value' => 'search'],
    
        'email'    => ['title' => 'Email', 'type' => 'string', 'compareType' => '%like%', 'validation' => ['maxLength' => 150]],
        'name'     => ['title' => 'Name', 'type' => 'string', 'compareType' => '%like%'],
        'username' => ['title' => 'Username', 'type' => 'string', 'compareType' => '%like%'],
        'user_id'  => ['title' => 'ID', 'type' => 'integer', 'compareType' => '=', 'validation' => ['max' => 10000000]],
    
        'role'       => ['title' => 'Role', 'type' => 'user_role', 'compareType' => '', 'source' => $roles],
        'status'     => ['title' => 'Status', 'type' => 'user_status', 'compareType' => '', 'source' => $statuses],
        'active'     => ['title' => 'Active', 'type' => 'user_active', 'compareType' => '', 'source' => $actives],
    
        'created_at'    => ['title' => 'Created At', 'type' => 'date', 'compareType' => 'like%'],
        'last_logged_at' => ['title' => 'Last Login', 'type' => 'date', 'compareType' => 'like%'],
    ];
    
    $query = User::orderByDesc('id');
    
    // Handle filters and prepare SQL query
    $filter       = Filter::init($query, $request, $filters, route('users.list'), route('users.list'), 'collapsed');
    $filter       = $filter::filter();
    $filterFields = $filter::getFilterFields();
    $query        = $filter::getQuery();
    
    // Sorting
    $sort      = $request->get('sort');
    $direction = $request->get('direction');
    
    // Pagination
    $pagination       = Pagination::init($query, 20, $sort, $direction, $filterFields)::paginate();
    $paginationFields = $pagination::getPaginationFields();
    $users            = $pagination::getRecords();
    
    return view('users.list', compact('users', 'filterFields', 'paginationFields'));
}
```

### Collapsed filter

![Collapsed filter](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/filter-collapsed.png)

### Expanded filter
![Expanded filter](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/filter-expanded.png)

### Pagination
![Pagination](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/paganation.png)
