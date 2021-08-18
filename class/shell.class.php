<?php

final class Shell {

    public static function execute($command){
        if(is_array($command)){
            $command = implode("\n", $command);
        }
        exec($command." 2>&1", $output, $code);
        if(is_array($output)){
            $output = implode("\n", $output);
        }
        return [
            "code" => $code,
            "output" => $output,
            "success" => !($code > 0)
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
        $windir = ($_SERVER["windir"] ?? $_SERVER["WINDIR"]);
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