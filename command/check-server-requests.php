<?php

require_once(__DIR__."/../default/handling.php");

try{
    $requests = SAST::service("check-new-requests", []);
    foreach($requests as $request){
        Request::createIfNotExists($request["id"], $request["data"]);
    }
}catch(Exception $exception){
    ErrorRecorder::recordFromException($exception);
}
