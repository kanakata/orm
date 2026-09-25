<?php
include "./orm.php";
(new Orm())->table("products")->select()->find();