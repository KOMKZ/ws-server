<?php
namespace cchat\base;

use Yii;
use yii\base\Object;
use GatewayWorker\Lib\Gateway;

/**
 *
 */
class Res extends Object
{
    CONST STATUS_SUCC = 1;
    CONST STATUS_ERR = 2;
    CONST STATUS_WARNING = 3;
    CONST STATUS_DANGER = 4;

    public static function format($data){
        return json_encode($data);
    }
    public static function getPingRes(){
        $res = self::getRouteRes();
        $res['route'] = 'live/ping';
        $res['body'] = '';
        return $res;
    }
    public static function getRouteRes(){
        return [
            'route' => null,
            'body' => [
                'status' => null,
                'message' => null,
                'data' => null
            ],
            'header' => self::getResHeader(),
            'utime' => time(),
        ];
    }
    public static function getEventRes(){
        return [
            'event' => null,
            'body' => [
                'status' => null,
                'message' => null,
                'data' => null
            ],
            'header' => self::getResHeader(),
            'utime' => time(),
        ];
    }
    public static function sendToCurrent($data){
        return Gateway::sendToCurrentClient(self::format($data));
    }
    public static function sendToClient($clientId, $data){
        return Gateway::sendToClient($clientId, $jsonData);
    }
    public static function sendToClients($clients, $data){
        $jsonData = self::format($data);
        foreach($clients as $clientId){
            Gateway::sendToClient($clientId, $jsonData);
        }
    }
    private static function getResHeader(){
        $reqHeader = Yii::$app->req->header;
        return $reqHeader;
    }
}
