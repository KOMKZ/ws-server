<?php
namespace wsserver\server\commands;

use wsserver\server\commands\Command;
use wsserver\base\Worker;

/**
 *
 */
class StartCommand extends Command
{
    static $name = 'start';
    public static function execute(){
        Worker::$action = static::$name;
        Worker::$daemonize = static::$daemonize;
        Worker::runAll();
    }
}
