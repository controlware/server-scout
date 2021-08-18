<?php

final class ErrorRecorder {

    static public function recordFromException(Exception $exception){
        self::recordFromText($exception->getMessage());
    }

    static public function recordFromText(String $text){
        Log::write($text);
        $dirname = self::directoryPath().date("Y-m-d");
        if(!is_dir($dirname)){
            mkdir($dirname, 0777, true);
        }
        $filename = date("H-i-s")."_".uniqid().".log";
        file_put_contents("{$dirname}/{$filename}", $text);
    }

    static public function cleanOldFiles(){
        $dirname = self::directoryPath();
        $filenames = scandir($dirname);
        $dates = [];
        foreach($filenames as $filename){
            if(strlen($filename) === 10){
                $dates[] = $filename;
            }
        }
        sort($dates);
        while(count($dates) > 10){
            Shell::removeDirectory($dirname.$dates[0]);
            unset($dates[0]);
            $dates = array_values($dates);
        }
    }

    static public function directoryPath(){
        return __DIR__."/../log/error/";
    }

}