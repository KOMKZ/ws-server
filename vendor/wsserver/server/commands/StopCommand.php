<?php
namespace wsserver\server\commands;

use wsserver\server\commands\Command;
use wsserver\base\Worker;

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
