# orm usage

## to make changes run

```cmd
php run
```

## select

```php
// fetch a single column
(new Orm())->table("table_name")->select()->find()

// fetch a all columns
(new Orm())->table("table_name")->select()->find_all()
```

## insert

```php

```

## delete

```php

```

## update

```php

```

## upsert

```php

```

## create database

```php
(new Orm())->create_database("database_name")
```

## drop database

```php
(new Orm())->drop_database("database_name")
```

## create table

```php
(new Orm())->create_table("table_name", function(){
    return [
        "id" => ["int", "not null"]
    ];
})
```

## drop table

```php
(new Orm())->drop_table("database_name")
```
