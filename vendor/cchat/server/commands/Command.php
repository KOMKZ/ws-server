<?php
namespace cchat\server\commands;

use yii\base\Object;
/**
 *
 */
class Command extends Object
{
    static public $name;
    static public $daemonize = false;
    public static function execute(){
        throw new \Exception('子类必须重写父类的execute方法' . __METHOD__);
    }
}
