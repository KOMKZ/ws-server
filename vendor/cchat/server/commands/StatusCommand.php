<?php
namespace cchat\server\commands;

use cchat\server\commands\Command;
use cchat\base\Worker;

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
