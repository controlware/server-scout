<?php

final class Request {
    
    static public function createIfNotExists(String $id, String $data){
        $filename = self::searchRequestFile($id);
        if($filename === false){
            file_put_contents(self::directoryPath()."/pending/{$id}.req", $data);
        }
        return true;
    }

    static public function directoryPath(){
        return __DIR__."/../request/";
    }

    static public function workOnNextRequest(){
        $filename = self::nextPedingRequestFile();
        if(!$filename){
            return true;
        }
        return self::workOnRequest($filename);
    }

    static public function workOnRequest(String $filename){
        // Define o nome dos arquivos
        $basename = basename($filename);
        $filename_working = __DIR__."/../request/working/{$basename}";
        $filename_done = __DIR__."/../request/done/{$basename}";

        // Move para a pasta 'working'
        Log::write("Iniciando trabalho no arquivo: {$basename}");
        rename($filename, $filename_working);

        // Captura o conteudo do arquivo
        $content = file_get_contents($filename_working);
        $request = json_decode($content, true);
        
        // Identifica a tarefa
        switch($request["task"]){
            // Limpa o servidor
            case "clean-server":
                $request["result"] = ["success" => Cleaner::cleanAll()];
                break;
            // Executa um comando comum no SO
            case "execute-command":
                $result = Shell::execute($request["data"]["command"]);
                $request["result"] = $result;
                break;
            // Atualiza o ServerScout
            case "update":
                $request["result"] = ["success" => Updater::updateServerScout()];
                break;
            // Comando nao identificado
            default:
                Log::write("Tipo de tarefa não identificado: {$request["task"]}");
                return false;
                break;
        }

        // Move para a pasta 'done'
        Log::write("Trabalho finalizado para o arquivo: {$basename}");
        file_put_contents($filename_done, json_encode($request));
        unlink($filename_working);
    }

    static public function nextPedingRequestFile(){
        $dirname = self::directoryPath()."/pending";
        $filenames = scandir($dirname);
        foreach($filenames as $filename){
            if(substr($filename, -4) === ".req"){
                return "{$dirname}/{$filename}";
            }
        }
        return false;
    }

    static public function searchRequestFile(String $id){
        $dirname = self::directoryPath();
        foreach(["pending", "working", "done"] as $subdir){
            $filename = "{$dirname}/{$subdir}/{$id}.req";
            if(file_exists($filename)){
                return $filename;
            }
        }
        return false;
    }

}