<?php

final class Updater {

    public static function updateServerScout(){
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
            return true;
        }else{
            Log::write("Houve uma falha na atualização.");
            return false;
        }
    }

}