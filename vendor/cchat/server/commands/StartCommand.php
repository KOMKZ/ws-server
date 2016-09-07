<?php
namespace cchat\server\commands;

use cchat\server\commands\Command;
use cchat\base\Worker;

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
