<?php

include "./Orm.php";

$data = new Orm();
$data->MySql("localhost", "nyathi", "root", "");
$data->select();