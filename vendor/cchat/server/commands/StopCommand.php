<?php
namespace cchat\server\commands;

use cchat\server\commands\Command;
use cchat\base\Worker;

/**
 *
 */
class StopCommand extends Command
{
    static public $name = "stop";
    public static function execute(){
        Worker::$action = static::$name;
        Worker::runAll();
    }
}
