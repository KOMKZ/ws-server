<?php
namespace wsserver\base;

use wsserver\base\Res;
use yii\base\Component;
use yii\base\BootstrapInterface;
use yii\base\Event as BaseEvent;

/**
 *
 */
class Event extends BaseEvent
{
    public $eventData = null;

    public function sendToClient($clientId, $data, $status = null, $message = null){
        $res = Res::getEventRes();
        $res['event'] = $this->name;
        $res['body']['status'] = $status;
        $res['body']['data'] = $data;
        $res['body']['message'] = $message;
        Res::sendToClient($clientId, $res);
    }
    public function sendToClients($clientIds, $data, $status = null, $message = null){
        $res = Res::getEventRes();
        $res['event'] = $this->name;
        $res['body']['status'] = $status;
        $res['body']['data'] = $data;
        $res['body']['message'] = $message;
        Res::sendToClients($clientIds, $res);
    }
    public function sendToCurrent($data, $status = null, $message = null){
        $res = Res::getEventRes();
        $res['event'] = $this->name;
        $res['body']['status'] = $status;
        $res['body']['data'] = $data;
        $res['body']['message'] = $message;
        Res::sendToCurrent($res);
    }

}
