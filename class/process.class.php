<?php

final class Process {

    private static $pidFileName = __DIR__."/../temporary/running.pid";

    static public function currentPID(){
        return getmypid();
    }

    static public function processIsRunning($pid){
        if(strlen($pid) === 0){
            return false;
        }
        return file_exists("/proc/{$pid}");
    }
    
    static public function registerThatIsRunning(){
        file_put_contents(self::$pidFileName, self::currentPID());
    }

    static public function lastRegistredPID(){
        if(!file_exists(self::$pidFileName)){
            return null;
        }
        return file_get_contents(self::$pidFileName);
    }

    static public function scoutIsRunning(){
        $pid = self::lastRegistredPID();
        return self::processIsRunning($pid);
    }

}