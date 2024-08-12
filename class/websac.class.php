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

    // Executa um truncate na tabela historico de todos os clientes
    static public function truncateAllHistory(){
        $postgresql = new PostgreSQL();
        $databases = $postgresql->listDataBases();
        foreach($databases as $database){
            $connection = $postgresql->connection($database);
            $res = $connection->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename = 'historico'");
            $count = $res->fetchColumn();
            if($count > 0){
                $connection->query("TRUNCATE TABLE historico");
            }
        }
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
