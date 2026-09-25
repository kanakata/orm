# Simple php based orm

## Available database support include:

- mysql
- pgsql
- sqlite

# Usage

## To make changes run:

```cmd
php run
```

## Selecting data

```php
// fetch a single column
(new Orm())->table("table_name")->select()->find()

// fetch a all columns
(new Orm())->table("table_name")->select()->find_all()
```

## Insert data

```php

```

## Deleting data

```php

```

## Updating data

```php

```

## Create a database

```php
(new Orm())->create_database("database_name")
```

## Dropping a database

```php
(new Orm())->drop_database("database_name")
```

## Creating a table

```php
(new Orm())->create_table("table_name", function(){
    return [
        "id" => ["int", "not null"]
    ];
})
```

## Dropping table

```php
(new Orm())->drop_table("database_name")
```
