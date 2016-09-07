<?php
namespace cchat\server\commands;

use cchat\server\commands\Command;
use cchat\base\Worker;

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
