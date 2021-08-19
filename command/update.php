<?php

require_once(__DIR__."/../default/handling.php");

Log::write("Iniciando atualização do Server Scout.");

$command = implode("; ", [
    "cd ".__DIR__."/..",
    "git fetch --all",
    "git reset --hard origin/main"
]);

Shell::execute($command);

$result = Shell::execute("cd ".__DIR__."/..; git pull");
if($result["output"] === "Already up-to-date."){
    Log::write("Atualizado com sucesso!");
}else{
    Log::write("Houve uma falha na atualização.");
}