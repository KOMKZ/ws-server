<?php
namespace wsserver\server\commands;

use wsserver\server\commands\Command;
use wsserver\base\Worker;

/**
 *
 */
class ReloadCommand extends Command
{
    static public $name = "reload";
    public static function execute(){
        Worker::$action = static::$name;
        Worker::$daemonize = static::$daemonize;
        Worker::runAll();
    }
}
