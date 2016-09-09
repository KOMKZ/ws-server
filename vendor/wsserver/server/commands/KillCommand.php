<?php
namespace wsserver\server\commands;

use wsserver\server\commands\Command;
use wsserver\base\Worker;

/**
 *
 */
class KillCommand extends Command
{
    static public $name = "kill";
    public static function execute(){
        Worker::$action = static::$name;
        Worker::runAll();
    }
}
