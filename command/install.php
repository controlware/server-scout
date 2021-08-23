<?php

/*

Atenção:
Esse arquivo deve ser independente, pois ele será executado sozinho.

Comando para iniciar a instalação:
yum install php php-posix -y
wget https://raw.githubusercontent.com/controlware/server-scout/main/command/install.php -O ~/install-serverscout.php
php ~/install-serverscout.php

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
write("Versão atual do PHP: ".phpversion());
if(version_compare(phpversion(), "7.0.0", "<")){
    write("Será atualizado para a versão 7.4");
    // Remove o PHP antigo
    execute("yum erase php* -y");

    // Instala o PHP 7.4
    execute("yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm -y");
    execute("yum install https://rpms.remirepo.net/enterprise/remi-release-7.rpm -y");
    execute("yum install yum-utils -y");
    execute("yum-config-manager --enable remi-php74");
    execute("yum install php php-cli php-posix -y");
}

// Instala o GIT, caso nao esteja instalado ainda
execute("yum install git -y");

// Baixa o projeto no diretorio escolhido
execute("git clone https://github.com/controlware/server-scout.git \"".PROJECT_PATH."\"");

// Verifica se o processo ja esta no crontab
$cron_filename = "/var/spool/cron/root";
$content = (file_exists($cron_filename) ? file_get_contents($cron_filename) : "");
if(strpos($content, "server-scout") === false){
    $content = "* * * * * php \"".PROJECT_PATH."/command/start.php\";\r\n{$content}";
    file_put_contents($cron_filename, $content);
    write("Processo configurado no Cron com sucesso.");
}else{
    write("Processo já está configurado no Cron.");
}

// Avisa que foi finalizado com sucesso
write("Instalação concluída com sucesso!");

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

    if($error){
        if(substr($command, 0, 4) === "yum " && strpos($output, "Error: Nothing to do") !== false){
            $error = false;
        }
    }
    
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
