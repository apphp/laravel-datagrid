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

### 3. Handle filters and prepare SQL builder
```php
$query = User::sortable()->orderByDesc('user_id');
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

## Example
![Open filter](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/filter-opened.png)
