<?php
namespace wsserver\server\commands;

use wsserver\server\commands\Command;
use wsserver\base\Worker;

/**
 *
 */
class StatusCommand extends Command
{
    static public $name = "status";
    public static function execute(){
        Worker::$action = static::$name;
        Worker::runAll();
    }
}
