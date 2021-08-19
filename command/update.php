<?php

require_once(__DIR__."/../default/handling.php");

$command = implode("; ", [
    "cd ".__DIR__."/..",
    "git fetch --all",
    "git reset --hard origin/main"
]);

Shell::execute($command);

$result = Shell::execute("cd ".__DIR__."/..; git pull");
var_dump($result);