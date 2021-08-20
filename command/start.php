<?php

/*
Como verificar se está em execução:
- Sempre que o programa executar, ele deve capturar o próprio PID
- Esse PID devera ser gravado num arquivo dentro do projeto
- Quando o crontab executar o programa, o programa deve verifciar esse arquivo, que contem o PID, e verificar se o processo ainda esta ativo


Um scout que esta em execucao deve verificar se o PID que esta no arquivo contem seu proprio PID, e caso nao seja, ele deve se encerrar sozinho
*/

require_once(__DIR__."/../default/handling.php");

if(Process::scoutIsRunning()){
    die();
}
Process::registerThatIsRunning();

$sast = new SAST();
$sast->startSocketConnection();
