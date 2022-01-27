<?php

final class Cleaner {

    static public function cleanAll(){
        self::cleanJunkFiles();
        self::cleanRemovedCustomers();
        return true;
    }

    static public function cleanJunkFiles(){
        $commands = [
            "rm -f /etc/httpd/logs/*",
            "find /var/log -type f -delete",
            "find /home -name \"*.backup\" -delete",
            "find /data/temp -name \"*.backup\" -delete",
            "find /data/comunica -name \"*.backup\" -delete"
        ];
        
        $arr_htdocs = ["/usr/local/apache2/htdocs", "/data/htdocs"];
        foreach($arr_htdocs as $htdocs){
            $commands[] = "find {$htdocs} -name *.*.*-*.log ! -mtime -3 | /usr/bin/xargs rm -f;";
            $commands[] = "for a in {$htdocs}/*/temp/pdfnfe/*; do rm -f \$a; done";
            $commands[] = "find {$htdocs}/ -name notaxml*.zip ! -mtime -2 | /usr/bin/xargs rm -f;";
            $commands[] = "find {$htdocs}/*/temp/fiscal/* ! -mtime -2 | /usr/bin/xargs rm -f;";
            $commands[] = "find {$htdocs} -name error.log -size +100M -delete";
            $commands[] = "find {$htdocs}/*/temp/kikker/kikker_uploaded -type f -mtime +10 -delete";
            $commands[] = "find {$htdocs}/*/temp/saurus -type f -mtime +10 -delete";
            $commands[] = "for a in {$htdocs}/*/temp/log-connection/*; do rm -f \$a; done";
            $commands[] = "rm -f {$htdocs}/*/temp/*.log";
            $commands[] = "rm -Rf {$htdocs}/*/temp/*/*.log";
            $commands[] = "rm -Rf {$htdocs}/*/temp/*.pdf";
            $commands[] = "rm -f {$htdocs}/*/temp/201*";
            $commands[] = "rm -f {$htdocs}/*/temp/202*";
            $commands[] = "find {$htdocs}/*/temp/saurus -type f -delete";
        }
        
        $arr_comunica = ["/home/publico/comunica", "/data/comunica"];
        foreach($arr_comunica as $comunica){
            $commands[] = "find {$comunica} -name \"SPED*.txt\" ! -mtime -1 -delete";
            $commands[] = "find {$comunica} -name \"*.xml\" ! -mtime -30 -delete";
            $commands[] = "find {$comunica}/*/*/pdv/IMPORTADO/* -type f ! -mtime -10 | /usr/bin/xargs rm -f;";
            $commands[] = "find {$comunica}/*/IMPORTADO/* -type d ! -mtime -30 -exec rm -Rf {} +;";
            $commands[] = "find {$comunica}/*/*/IMPORTADO/* -type d ! -mtime -30 -exec rm -Rf {} +;";
            $commands[] = "find {$comunica}/*/*/*/IMPORTADO/* -type d ! -mtime -30 -exec rm -Rf {} +;";
            $commands[] = "find {$comunica}/*/*/*/*/IMPORTADO/* -type d ! -mtime -30 -exec rm -Rf {} +;";
            $commands[] = "find {$comunica} -name \"*.exe\" -delete";
            $commands[] = "rm -f {$comunica}/*.TXT";
            $commands[] = "rm -f {$comunica}/*.xml";
            $commands[] = "rm -f {$comunica}/*.pdf";
        }
    
        foreach($commands as $command){
            Shell::execute($command);
        }

        return true;
    }

    static public function cleanRemovedCustomers(){
        $postgresql = new PostgreSQL();
        $databases = $postgresql->listDataBases();
        unset($postgresql);
        if(!is_array($databases) || count($databases) == 0){
            return false;
        }
        $directories = ["/data/comunica", "/data/htdocs"];
        $ignore = [".", "..", "includes"];
        foreach($directories as $directory){
            $files = scandir($directory);
            foreach($files as $file){
                if(is_dir("{$directory}/{$file}") && !in_array($file, $ignore) && !in_array($file, $databases)){
                    Log::write("Removendo diretório: {$directory}/{$file}");
                    Shell::execute("rm -Rf \"{$directory}/{$file}\"");
                }
            }
        }
        return true;
    }

}
