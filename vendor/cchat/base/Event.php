<?php
namespace cchat\base;

use cchat\base\Res;
use yii\base\Component;
use yii\base\BootstrapInterface;

/**
 *
 */
class Event extends Component implements BootstrapInterface
{
    public $install = [];
    static public $handlers = [];
    public function bootstrap($app){

    }
    public function init(){
        foreach($this->install as $item){
            if(count($item) >= 2 && class_exists($item[0]) && method_exists($item[0], $item[1])){
                $handlers = call_user_func_array($item, []);
                foreach($handlers as $name => $handler){
                    $this->installHandler($name, $handler);
                }
            }else{
                throw new \Exception('check install events definations ' . implode(',', $item) . " doesn't exists");
            }
        }
    }
    public function installHandler($name, $handler){
        if(count($handler) >= 2 && class_exists($handler[0]) && method_exists($handler[0], $handler[1])){
            if(!array_key_exists($name, self::$handlers)){
                self::$handlers[$name] = $handler;
            }else{
                throw new \Exception("event name {$name} has exists , in " . implode(',', $handler) . ' ' . __METHOD__);
            }
        }else{
            throw new \Exception("class " . $handler[0] . " raise error when install event handler");
        }
    }
    public function touch($name, $params = []){
        if(array_key_exists($name, self::$handlers)){
            $handler = self::$handlers[$name];
            array_push($params, $name);
            call_user_func_array($handler, $params);
        }
    }
    public function getEvents(){
        return array_keys(self::$handlers);
    }
}
