<?php
namespace common\models;

use wsserver\base\Model;
use common\base\AppEvent;
use common\base\Res;

/**
 *
 */
class UserModel extends Model
{
    protected function attachEvents(){
        if(!static::$EVENT_ATTACH){
            $this->on(AppEvent::SOME_ONE_LOGIN, [$this, 'boardGroupPerson']);
        }
    }
    public function auth(){
        return true;
    }
    public function login(){
        $this->touch(AppEvent::SOME_ONE_LOGIN, ['cmd' => 'shutdown/now']);
        return true;
    }
    protected function boardGroupPerson($event){
        console($event->eventData, '~');
        $event->sendToCurrent(['cmd' => 'shutdown/now']);
    }
}
