<?php
namespace cchat\server\commands;

use cchat\server\commands\Command;
use cchat\base\Worker;

/**
 *
 */
class RestartCommand extends Command
{
    static $name = 'restart';
    public static function execute(){
        Worker::$action = static::$name;
        Worker::$daemonize = static::$daemonize;
        Worker::runAll();
    }
}
