<?php

final class PostgreSQL {

    private $connection;
    private $password;

    public function __construct(){
        $this->password = (Shell::isWindows() ? "postgres" : "Controlware@1987");
        $this->verifyEnvironment();
    }

    public function connection(){
        if(is_object($this->connection)){
            if(!$this->connection->query("SELECT current_timestamp")){
                unset($this->connection);
            }
        }
        if(!is_object($this->connection)){
            $this->connection = new PDO("pgsql:host=127.0.0.1 port=5432 dbname=postgres user=postgres password={$this->password}");
            if(!is_object($this->connection)){
                Log::write("Houve uma falha ao conectar com o banco de dados.");
                return false;
            }
        }
        return $this->connection;
    }

    private function verifyEnvironment(){
        $extensions = ["pgsql", "pdo"];
        foreach($extensions as $extension){
            if(!extension_loaded($extension)){
                Shell::execute("yum install php-{$extension} -y");
            }
        }
        return true;
    }

    public function listDataBases(){
        $connection = $this->connection();
        if(!$connection){
            return false;
        }
        $res = $connection->query("SELECT datname FROM pg_database ORDER BY datname");
        $arr = $res->fetchAll(2);
        $databases = [];
        foreach($arr as $row){
            $databases[] = $row["datname"];
        }
        return $databases;
    }

}
