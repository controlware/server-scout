<?php

require_once(__DIR__."/../default/handling.php");

try{
    Request::workOnNextRequest();
}catch(Exception $exception){
    ErrorRecorder::recordFromException($exception);
}
