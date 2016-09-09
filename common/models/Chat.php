<?php
namespace common\models;

use Yii;
use yii\base\Model;
use wsserver\base\Res;
use common\models\Chanel;
use common\models\User;

/**
 *
 */
class Chat extends Model
{
    const E_NEW_PRI_MSG = 'new_pri_msg';
    const E_NEW_CHANEL_MSG  = 'new_chanel_msg';




    public function checkSayInterval($userInfo, $chanel){
        if(!is_array($userInfo['chanel_say_interval']) || !array_key_exists($chanel['chanel_id'], $userInfo['chanel_say_interval']) ){
            return true;
        }
        if(is_array($userInfo['chanel_say_interval']) &&
           array_key_exists($chanel['chanel_id'], $userInfo['chanel_say_interval']) &&
           // 发言的时间 < 下次能够发言的时间  则不能发言
           time()  < ($chanel['say_interval'] + $userInfo['chanel_say_interval'][$chanel['chanel_id']])
           ){
            $this->addError('say_interval_limit', "组{$chanel['chanel_id']}中每{$chanel['say_interval']}秒才能发言一次");
            return false;
        }
        return true;
    }

    public function recordSayInterval($chanelInfo){

        $chanelSayRecord = User::getUserAttr('chanel_say_interval');
        if(!is_array($chanelSayRecord)){
            $chanelSayRecord = [];
        }
        $chanelSayRecord[$chanelInfo['chanel_id']] = time();
        User::setUserAttr('chanel_say_interval', $chanelSayRecord);
    }

    public function sayInChanel($chanelId, $data){
        $chanel = Chanel::getChanel($chanelId);
        $userInfo = Yii::$app->user->getIdentity();
        if($chanel && $userInfo){
            if($this->checkSayInterval($userInfo, $chanel)){
                $maxLength = 100;
                if(mb_strlen($maxLength, 'utf8') > $maxLength){
                    $this->addError("文本超过{$maxLength}个字");
                    return false;
                }
                if($this->publishInChanel($chanel, $data)){
                    $this->recordSayInterval($chanel);
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            $this->addError('chanel_id', "指定的频道{$chanelId}不存在 或者 用户未登录");
            return false;
        }
    }



    protected function publishInChanel($chanel, $data){
        // 注意 这里的Data不许是上层检查过的
        $chanelModel = new Chanel();
        if($chanel){
            $userInfo = Yii::$app->user->getIdentity();
            if($chanelModel->canSayInChanel($chanel, $userInfo)){
                // 转发到组的人
                $targets = $chanelModel->getClientIdInChanel($chanel['chanel_id'], [$userInfo['client_id']]);
                $params = [$data, User::getSafeInfo($userInfo), $targets];
                Yii::$app->event->touch(self::E_NEW_CHANEL_MSG, $params);
                return true;
            }else{
                $this->addErrors($chanelModel->getErrors());
                return false;
            }
        }else{
            $this->addError('chanel_id', "指定的频道{$chanelId}不存在");
            return false;
        }
    }
    public static function eventNewChanelMsg($data, $sendUser, $targets, $eventName){
        $res = Res::getEventRes();
        $res['event'] = $eventName;
        $res['body'] = [
            'status' => Res::STATUS_SUCC,
            'data' => [
                'body' => $data,
                'user_info' => $sendUser
            ],
            'message' => null,
        ];
        return Res::sendToClients($targets, $res);
    }
    public static function eventNewPirMsg($status, $data, $error, $target, $eventName){
        $res = Res::getEventRes();
        $res['event'] = $eventName;
        $res['body'] = [
            'status' => $status,
            'data' => $data,
            'message' => $error
        ];
        return Res::sendToClients($target, $res);
    }
    public static function installEventHandler(){
        return [
            self::E_NEW_PRI_MSG => [static::className(), 'eventNewPirMsg'],
            self::E_NEW_CHANEL_MSG => [static::className(), 'eventNewChanelMsg'],
        ];
    }
}
