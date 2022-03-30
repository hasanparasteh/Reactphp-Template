<?php

namespace Tests;

class Runner
{
    public static function stop()
    {
        shell_exec("pkill -f server.php");
    }

    public static function run()
    {
        self::stop();
        $PATH = dirname(__DIR__, 1);
        $command = "nohup php $PATH/server.php";
        exec(sprintf("%s > %s 2>&1 & echo $!", $command, "/tmp/parsa.log"));
        sleep(5);
    }
}