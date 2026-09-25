<?php
include "./query_builder.php";
// print_r((new QueryBuilder())->table("products")->select()->order_by(["id"], ["desc"])->find_all());
// print_r((new QueryBuilder())->table("products")->select()->order_by(["id"], ["desc"])->limit(10,2)->find_all());
// print_r((new QueryBuilder())->table("products")->select()->limit(10,2)->find_all());
// print_r((new QueryBuilder())->table("products")->count()->count_column());
// print_r((new QueryBuilder())->table("products")->avg("id")->count_column());
// print_r((new QueryBuilder())->table("products")->sum("id")->count_column());
// print_r((new QueryBuilder())->table("products")->min("id")->count_column());
// print_r((new QueryBuilder())->table("products")->max("id")->count_column());