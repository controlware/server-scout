<?php

require_once(__DIR__."/../default/handling.php");

$postgresql = new PostgreSQL();
var_dump($postgresql->listDataBases());
