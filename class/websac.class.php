<?php

final class WebSac {

    // Retorna o local da pasta htdocs
    static public function htdocsPath(){
        $htdocsPossibilities = ["/data/htdocs" , "/usr/local/apache2/htdocs"];
        foreach($htdocsPossibilities as $htdocs){
            if(is_dir($htdocs)){
                return $htdocs;
            }
        }
        Log::write("Não foi possível localizar o diretório htdocs do Apache.");
        return null;
    }

    // Retorna a lista com o nome dos clientes, olhando a pasta htdocs
    static public function listWebSacDirectories(){
        $htdocs = self::htdocsPath();
        if(!$htdocs){
            return false;
        }

        $websacDirectories = [];
        $filenames = scandir($htdocs);
        foreach($filenames as $filename){
            if(in_array($filename, [".", ".."])){
                continue;
            }
            if(is_dir("{$htdocs}/{$filename}/v3")){
                $websacDirectories[] = $filename;
            }
        }

        return $websacDirectories;
    }

    // Atualiza todos os WebSacs
    static public function updateAll(){
        $websacList = self::listWebSacDirectories();
        foreach($websacList as $websac){
            Shell::execute("wget --delete-after http://localhost/{$websac}/ajax/login_update.php");
        }
        return true;
    }

}
