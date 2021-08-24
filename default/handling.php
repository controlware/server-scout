<?php

// Inclusao dos arquivos padroes
require_once(__DIR__."/function.php");

// Auto-register das classes
spl_autoload_register(function($classname){
    $filename = strtolower($classname);
    $classFilename = __DIR__."/../class/{$filename}.class.php";
    if(file_exists($classFilename)){
        require_once($classFilename);
    }
});