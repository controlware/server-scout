<?php

final class Shell {

    public static function execute($commands){
        if(!is_array($commands)){
            $commands = [$commands];
        }
        $code = $output = null;
        $success = false;
        foreach($commands as $command){
            $command = html_entity_decode($command, ENT_QUOTES);
            Log::write("Comando: {$command}");
            exec($command." 2>&1", $output, $code);
            if(is_array($output)){
                $output = implode("\n", $output);
            }
            $success = !($code > 0);
        }
        return [
            "code" => $code,
            "output" => $output,
            "success" => $success
        ];
    }

    public static function executePHP(String $filename){
        if(self::isWindows()){
            pclose(popen("start /B php \"{$filename}\" > NUL", "r"));
        }else{
            exec("php \"{$filename}\" > /dev/null &");
        }
    }

    public static function isWindows(){
        $windir = ($_SERVER["windir"] ?? $_SERVER["WINDIR"] ?? "");
        return (strpos(strtolower($windir), "windows") !== false);
    }

    public static function removeDirectory(String $dirname){
        $files = array_diff(scandir($dirname), [".", ".."]);
        foreach($files as $file){
            if(is_dir("{$dirname}/{$file}")){
                self::removeDirectory("{$dirname}/{$file}");
            }else{
                unlink("{$dirname}/{$file}");
            }
        }
        return rmdir($dirname);
    }

}