# Simple php based orm

## Available database support include:

- mysql
- pgsql
- sqlite

# Usage

## Selecting data

```php

(new Orm())->table("table_name")->select()->find()

(new Orm())->table("table_name")->select()->find_all()

(new QueryBuilder())->table("products")->select()->order_by(["id"], ["desc"])->find_all();

(new QueryBuilder())->table("products")->select()->order_by(["id"], ["desc"])->limit(10,2)->find_all();

(new QueryBuilder())->table("products")->select()->limit(10,2)->find_all();

(new QueryBuilder())->table("products")->count()->count_column();

(new QueryBuilder())->table("products")->avg("id")->count_column();

(new QueryBuilder())->table("products")->sum("id")->count_column();

(new QueryBuilder())->table("products")->min("id")->count_column();

(new QueryBuilder())->table("products")->max("id")->count_column();
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
