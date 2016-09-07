<?php
namespace cchat\server\commands;

use cchat\server\commands\Command;
use cchat\base\Worker;

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
