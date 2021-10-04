<?php

final class Log {

    static public function write(String $text){
        $text = date("H:i:s")." {$text}";
		if(strpos($text, "\n") !== false){
			$text = "\n{$text}\n";
		}
		$text = "{$text}\n";

        echo $text;
		
        $dirname = __DIR__."/../log/";
        if(!is_dir($dirname)) mkdir($dirname);
        $file = fopen($dirname.date("Y-m-d").".log", "a+");
        fwrite($file, $text);
        fclose($file);
    }

}