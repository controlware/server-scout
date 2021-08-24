<?php

function socket_read_all($socket){
    $data = "";
    while($input = socket_read($socket, 1024)){
        $data .= $input;
    }
    return (strlen($data) > 0 ? $data : $input);
}