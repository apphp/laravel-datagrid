[![License: MIT](https://img.shields.io/badge/License-MIT-brightgreen.svg)](https://opensource.org/licenses/MIT)


# DataGrid helpers for Laravel Framework Applications

This package helps to create DataGrid (CRUD) pages for Laravel 6+ framework applications.


## Requirements

* PHP >=7.1
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

If you need to modify the datagrid files, you can run:

```bash
php artisan vendor:publish --provider="Apphp\DataGrid\DataGridServiceProvider"
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

| Attribute        | Type       | Description                                                                  
|------------------|------------|-------------------------------------------------------------------
| `title`          | String     | Specifies a title, that will be shown in the label of filter field         
| `type`           | String     | Specifies a type of the filter field (see above)         
| `compareType`    | String     | Specifies which type of comparison will be used: ex.: '=', '%like%', '!=' etc.         
| `source`         | Array      | Specifies the source (array) to 'set' fields         
| `validation`     | Array      | Specifies validation rules (array). Possible options: ['minLength'=>2, 'maxLength'=>10, 'min'=>2, 'max'=>100]           
| `relation`       | String     | Specifies the relation between 2 models (One-to-One, One-to-Many), ex.: search in posts for users - relation="posts"          
| `relationXref`   | String     | Specifies the relation between 2 models (Many-to-Many), ex.: search in roles for users - relation="roles"         
| `htmlOptions`    | Array      | Specifies any possible HTML attribute for the field          
| `disabled`       | Boolean    | Specifies whether the field is disabled or not (default - not)           
  

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

@if(count($records))
    {!! \Apphp\DataGrid\Filter::renderFields() !!}
    
        <!-- YOUR TABLE WITH RECORDS DATA -->
        @foreach ($records as $record)
        <!-- ... -->
        @endforeach
        <!-- YOUR TABLE WITH RECORDS DATA -->

    {!! \Apphp\DataGrid\Pagination::renderLinks() !!}
@else
    {!! \Apphp\DataGrid\Message::warning('Sorry, no records were found. Please adjust your search criteria and try again.') !!}
@endif
```


## Configuration

To change default settings and enable some extra features you can export the config file:
```bash
php artisan vendor:publish --tag=laravel-datagrid:config
```


## Customize Views 

To change HTML template of the datagrid or use your own, publish view file and customize it to suit your needs.
```bash
$ php artisan vendor:publish --tag=laravel-datagrid:views
```
Now you should have a datagrid.php file in the config folder of your application. If you need to force to re-publish the config file to use `--force`.


## Testing 

To rum unit testing simply do following:
```bash
./vendor/bin/phpunit vendor\\apphp\\laravel-datagrid\\tests\\TestDataGridMessage.php
```

or your may add additional section to your composer.json file:
```json
"scripts": {
    "tests": "phpunit --colors=always",
    "test": "phpunit --colors=always --filter",
}
```

and then rum unit following command:
```bash
composer tests vendor\\apphp\\laravel-datagrid\\tests\\TestDataGridMessage.php
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

### Sorting 

If you use some kind of packages for column sorting, like `kyslik/column-sortable`, you have to change usage of Model to following:
Without sorting
```php
$query = User::orderByDesc('id');
```
With column sorting
```php
$query = User::sortable()->orderByDesc('id');
```

### Table content rendering

You have 2 way to render table content.
The first is to write creating table manually in view file. Look on example below:

```html
<div class="table-responsive">
    <table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="text-right" width="60px">@sortablelink('user_id', 'ID')</th>
        ...
    </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td class="text-right">{{ $user->user_id }}</td>
                ...
                </tr>
            @endforeach
    </tbody>
    </table>
</div>
``` 

The second way is to use <code>GridView</code> helper. Look on example below:
```php
// GridView - initialized in Controller
$gridView = GridView::init($records);

return view('backend.users', compact(... , 'gridView'));
```

```html
{{-- Render table content --}}
{!!
    $gridView::renderTable([
        'user_id'           => ['title' => 'ID', 'width'=>'60px', 'headClass'=>'text-right', 'class'=>'text-right'],
        'username'          => ['title' => 'Username', 'width'=>'', 'headClass'=>'text-left', 'class'=>''],
        'name'              => ['title' => 'Name', 'width'=>'', 'headClass'=>'text-left', 'class'=>''],
        'email'             => ['title' => 'Email', 'width'=>'', 'headClass'=>'text-left', 'class'=>'text-truncate px-2'],
        'created_at'        => ['title' => 'Created At', 'width'=>'160px', 'headClass'=>'text-center', 'class'=>'text-center px-1'],
        'last_login_at'     => ['title' => 'Last Login', 'width'=>'160px', 'headClass'=>'text-center', 'class'=>'text-center px-1'],
    ])
!!}
```


### Collapsed filter

![Collapsed filter](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/filter-collapsed.png)

### Expanded filter
![Expanded filter](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/filter-expanded.png)

### Pagination
![Pagination](https://raw.githubusercontent.com/apphp/laravel-datagrid/master/images/paganation.png)
