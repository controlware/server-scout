<?php

/*

Atenção:
Esse arquivo deve ser independente, pois ele será executado sozinho.

Comando para iniciar a instalação:
yum install php php-posix -y
wget --no-cache --no-cookies https://raw.githubusercontent.com/controlware/server-scout/main/command/install.php -O ~/install-serverscout.php
php ~/install-serverscout.php


1. Verificar se ele mesmo ja nao esta instalado, e caso esteja, indica o local e aborta a operacao

2. Verificar versao instalada do PHP, desinstalar a antiga e instalar a atual

3. Verificar se o git esta instalado, e instalar caso não esteja

4. Verificar qual o local ideal para se instalar

5. Executa comando do git para baixar repositorio

6. Cria crontab que verifica se o scout esta em execuxao, ele mesmo ja faz a primeira execucao


Como verificar se está em execução:
- Sempre que o programa executar, ele deve capturar o próprio PID
- Esse PID devera ser gravado num arquivo dentro do projeto
- Quando o crontab executar o programa, o programa deve verifciar esse arquivo, que contem o PID, e verificar se o processo ainda esta ativo


Um scout que esta em execucao deve verificar se o PID que esta no arquivo contem seu proprio PID, e caso nao seja, ele deve se encerrar sozinho

*/

// Define as constantes
define("PROJECT_NAME", "server-scout");

// Verifica se esta sendo executado como root
$user = posix_getpwuid(posix_geteuid());
if($user["name"] !== "root"){
    write("Usuário atual de execução é \"{$user["name"]}\", o correto deveria ser \"root\".");
    die();
}

// Define os diretorios preferenciais para instalcao
$preferred_directories = ["/data", "/etc"];

// Verifica se ja esta instalado, e caso nao, define diretorio de instalacao
foreach($preferred_directories as $directory){
    if(is_dir("{$directory}/".PROJECT_NAME)){
        write("Server Scout já se encontra instalado em: {$directory}/".PROJECT_NAME);
        die();
    }
    if(is_dir($directory)){
        define("PROJECT_PATH", "{$directory}/".PROJECT_NAME);
        break;
    }
}
if(!defined("PROJECT_PATH")){
    write("Não foi possível definir o diretório de instalação do Server Scout.\nDiretórios verificados: ".implode(", ", $preferred_directories));
    die();
}
write("Diretório de instalação: ".PROJECT_PATH);

// Verifica se a versao do PHP eh menor que 7
if(version_compare(phpversion(), "7.0.0", "<")){
    // Remove o PHP antigo
    execute("yum erase php* -y");

    // Instala o PHP 7.4
    execute("yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm -y");
    execute("yum install https://rpms.remirepo.net/enterprise/remi-release-7.rpm -y");
    execute("yum install yum-utils -y");
    execute("yum-config-manager --enable remi-php74");
    execute("yum update -y");
    execute("yum install php php-cli -y");
}


// Funcao que da saidas de texto
function write($text){
    echo "{$text}\n";
}

function execute($command, $dieOnError = true){
    write("Executando comando: {$command}");
    if(is_array($command)){
        $command = implode("\n", $command);
    }
    exec($command." 2>&1", $output, $code);
    if(is_array($output)){
        $output = implode("\n", $output);
    }
    $error = ($code > 0);
    
    if($error && $dieOnError){
        write("Falha ao executar o comando:\n{$output}");
        var_dump([
            "code" => $code,
            "output" => $output
        ]);
        die();
    }

    return $output;
}