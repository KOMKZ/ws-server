<?php
namespace wsserver\base;

use yii\base\Model as Base;
use wsserver\base\Event;

/**
 *
 */
class Model extends Base
{
    public static $EVENT_ATTACH = false;
    public function init(){
        parent::init();
        $this->attachEvents();
    }
    protected function attachEvents(){}
    protected function touch($name, $eventData = null){
        $event = new Event();
        $event->eventData = $eventData;
        $this->trigger($name, $event);

    }
}
