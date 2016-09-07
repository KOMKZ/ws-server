<?php
namespace common\models;

use Yii;
use cchat\base\IdentityInterface;
use yii\base\Model;
use GatewayWorker\Lib\Gateway;
use cchat\helpers\Curl;


/**
 *
 */
class User extends Model implements IdentityInterface
{
    static public $userInfo = [];
    public static function getIdentity(){
        $clientId = Yii::$app->req->clientId;
        return Gateway::getSession($clientId);
    }
    public static function findIdentity($id){

    }

    public static function getOwnInfo(){
        $clientId = Yii::$app->req->clientId;
        return Gateway::getSession($clientId);
    }
    public static function getProtectedAttr(){
        return ['client_id', 'is_root', 'assign_id'];
    }
    public static function logout(){
        $clientId = self::getOwnInfo()['client_id'];
        Gateway::closeClient($clientId);
    }
    public function login(){
        $clientId = Yii::$app->req->clientId;
        $assignId = 1;
        $userInfo = [
            'user_id' => mt_rand(111111, 999999),
            'user_name' => 'kitral' . mt_rand(111111, 999999),
            'is_guest' => false,
            'client_id' => $clientId,
            'assign_id' => $assignId,
            'is_root' => $assignId === 1,
            'chanel_say_interval' => null,
        ];
        Gateway::setSession($clientId, $userInfo);
        return true;
    }
    public static function setUserAttr($attr, $value){
        $userInfo = self::getOwnInfo();
        $userInfo[$attr] = $value;
        $clientId = Yii::$app->req->clientId;
        Gateway::setSession($clientId, $userInfo);
    }
    public static function getUserAttr($attr){
        $userInfo = self::getOwnInfo();
        if(array_key_exists($attr, $userInfo)){
            return $userInfo[$attr];
        }else{
            return null;
        }
    }
    public static function getSafeInfo($userInfo){
        $protectedAttr = User::getProtectedAttr();
        foreach($protectedAttr as $attr){
                unset($userInfo[$attr]);
        }
        return $userInfo;
    }

    public static function ifClientNoLogin($clientId){
        if(Yii::$app->user->getIsGuest()){
            console('结束掉', '~');
            Gateway::closeClient($clientId);
        }
    }
}
