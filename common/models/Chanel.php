<?php
namespace common\models;

use Yii;
use yii\base\Model;
use GatewayWorker\Lib\Gateway;
use yii\helpers\ArrayHelper;
use common\models\User;
use wsserver\base\Res;
use common\datas\RedisChanel;

/**
 *
 */
class Chanel extends Model
{

    const E_NEW_JOIN_CHANEL = 'new_join_chanel';

    public function setDenySay($chanelId, $status){
        $chanel = self::getChanel($chanelId);
        if($chanel){
            $status = (int)$status === 0 ? 0 : 1;
            $result = RedisChanel::updateAll(['deny_say' => $status], ['chanel_id' => $chanelId]);
            $chanel['deny_say'] = $status;
            return true;
        }else{
            $this->addError('chanel_id', "指定的频道{$chanel['chanel_id']}不存在");
            return false;
        }
    }

    public function setAllowJoin($chanelId, $status){
        $chanel = self::getChanel($chanelId);
        if($chanel){
            $status = (int)$status === 0 ? 0 : 1;
            $result = RedisChanel::updateAll(['allow_join' => $status], ['chanel_id' => $chanelId]);
            $chanel['allow_join'] = $status;
            return true;
        }else{
            $this->addError('chanel_id', "指定的频道{$chanel['chanel_id']}不存在");
            return false;
        }
    }

    public function appendToJoinBlackList($chanelId, $userId){
        $chanel = self::getChanel($chanelId);
        if($chanel && $userId){
            if(!is_array($chanel['join_black_list'])){
                $chanel['join_black_list'] = [];
            }
            if(!in_array($userId, $chanel['join_black_list'])){
                $chanel['join_black_list'][] = $userId;
            }
            $blacklist = implode(',', $chanel['join_black_list']);
            $result = RedisChanel::updateAll(['join_black_list' => $blacklist], ['chanel_id' => $chanelId]);
            return true;
        }else{
            $this->addError('chanel_id', "指定的频道{$chanel['chanel_id']}不存在");
            return false;
        }
    }

    public function removeFromJoinBlackList($chanelId, $userId){
        $chanel = self::getChanel($chanelId);
        if($chanel && $userId){
            if(!is_array($chanel['join_black_list'])){
                $chanel['join_black_list'] = [];
            }
            if(is_array($chanel['join_black_list']) && in_array($userId, $chanel['join_black_list'])){
                unset($chanel['join_black_list'][array_search($userId, $chanel['join_black_list'])]);
                $blacklist = implode(',', $chanel['join_black_list']);
                $result = RedisChanel::updateAll(['join_black_list' => $blacklist], ['chanel_id' => $chanelId]);
            }
            return true;
        }else{
            $this->addError('chanel_id', "指定的频道{$chanel['chanel_id']}不存在");
            return false;
        }
    }

    public function appendToSayBlackList($chanelId, $userId){
        $chanel = self::getChanel($chanelId);
        if($chanel && $userId){
            if(!is_array($chanel['say_black_list'])){
                $chanel['say_black_list'] = [];
            }
            if(!in_array($userId, $chanel['say_black_list'])){
                $chanel['say_black_list'][] = $userId;
            }
            $blacklist = implode(',', $chanel['say_black_list']);
            $result = RedisChanel::updateAll(['say_black_list' => $blacklist], ['chanel_id' => $chanelId]);
            return true;
        }else{
            $this->addError('chanel_id', "指定的频道{$chanel['chanel_id']}不存在");
            return false;
        }
    }

    public function removeFromSayBlackList($chanelId, $userId){
        $chanel = self::getChanel($chanelId);
        if($chanel && $userId){
            if(!is_array($chanel['say_black_list'])){
                $chanel['say_black_list'] = [];
            }
            if(is_array($chanel['say_black_list']) && in_array($userId, $chanel['say_black_list'])){
                unset($chanel['say_black_list'][array_search($userId, $chanel['say_black_list'])]);
                $blacklist = implode(',', $chanel['say_black_list']);
                $result = RedisChanel::updateAll(['say_black_list' => $blacklist], ['chanel_id' => $chanelId]);
            }
            return true;
        }else{
            $this->addError('chanel_id', "指定的频道{$chanel['chanel_id']}不存在");
            return false;
        }
    }

    public function joinPublicChanel($chanelId){
        $userInfo = Yii::$app->user->getIdentity();
        if($this->isChanelCanJoin($chanelId, $userInfo)){
            Gateway::joinGroup($userInfo['client_id'], $chanelId);
            $usersInChanel = ArrayHelper::getColumn($this->getChanelUsers($chanelId, true), 'client_id');
            unset($usersInChanel[$userInfo['client_id']]);

            $userInfo = User::getSafeInfo($userInfo);

            Yii::$app->event->touch(self::E_NEW_JOIN_CHANEL, [
                $userInfo,
                $chanelId,
                array_values($usersInChanel),
            ]);
            return true;
        }else{
            return false;
        }
    }

    public function createChanel($data){
        if(empty($data)){
            $this->addError('empty', '数据不能为空');
            return false;
        }
        $protectedAttr = ['admin_user_id', 'created_at', 'updated_at', 'extra', 'chanel_id'];
        foreach($protectedAttr as $attr){
            if(array_key_exists($attr, $data)){
                unset($data[$attr]);
            }
        }
        $formatArray = ['admin_user_id', 'say_white_list', 'say_black_list', 'join_white_list', 'join_black_list'];
        foreach($formatArray as $attr){
            if(array_key_exists($attr, $data) && is_array($data[$attr])){
                $data[$attr] = implode(',', $data[$attr]);
            }
        }

        $data['admin_user_id'] = Yii::$app->user->identity['user_id'];
        $data['created_at'] = time();
        $data['updated_at'] = time();
        $data['extra'] = '';

        $chanel = new RedisChanel();
        if($chanel->load($data, '') && $chanel->save()){
            return $chanel->toArray();
        }else{
            $this->addErrors($chanel->getErrors());
            return false;
        }
    }

    public function updateChanel($chanelId, $data){
        $one = self::getChanel($chanelId);
        if($one){
            $protectedAttr = ['chanel_id', 'created_at', 'updated_at', 'admin_user_id'];
            foreach($protectedAttr as $attr){
                if(array_key_exists($attr, $data)){
                    unset($data[$attr]);
                }
            }
            $formatArray = ['admin_user_id', 'say_white_list', 'say_black_list', 'join_white_list', 'join_black_list'];
            foreach($formatArray as $attr){
                if(array_key_exists($attr, $data) && is_array($data[$attr])){
                    $data[$attr] = implode(',', $data[$attr]);
                }
            }
            $one['updated_at'] = time();
            $attributes = (new RedisChanel)->attributes();
            foreach($data as $key => $value){
                if(!in_array($key, $attributes)){
                    unset($data[$key]);
                }
            }
            RedisChanel::updateAll($data, ['chanel_id' => $chanelId]);
            return true;
        }else{
            $this->addError('chanel_id', '指定的chanel不存在');
            return false;
        }
    }
    public static function getChanel($chanelId){
        $one = RedisChanel::find()->where(['chanel_id' => $chanelId])->asArray()->one();
        $formatArray = ['admin_user_id', 'say_white_list', 'say_black_list', 'join_white_list', 'join_black_list'];
        foreach($formatArray as $attr){
            if(!empty($one[$attr])){
                $one[$attr] = explode(',', trim($one[$attr]));
            }
        }
        return $one;
    }

    public function checkUserInChanel($userInfo, $chanelId){
        $users = $this->getChanelUsers($chanelId);
        if(!empty($users)){
            return in_array($userInfo['user_id'], $users);
        }else{
            return false;
        }
    }
    public function checkIsAdmin($chanelInfo, $userId){
        $admin = is_array($chanelInfo['admin_user_id']) && in_array($userId, $chanelInfo['admin_user_id']);
        return Yii::$app->user->identity['is_root'] || $admin;
    }
    public function canSayInChanel($chanelInfo, $userInfo){
        // 用户是否在当前组中
        $chanelId = $chanelInfo['chanel_id'];
        $userId = $userInfo['user_id'];
        if(!$this->checkUserInChanel($userInfo, $chanelId)){
            $this->addError('no-in-chanel', "您当前不在组{$chanelId}中, 不能发言");
            return false;
        }
        if($this->checkIsAdmin($chanelInfo, $userId)){
            return true;
        }
        if($chanelInfo){
            if(!$chanelInfo['deny_say']){
                // 白名单和黑名单
                if(is_array($chanelInfo['say_white_list']) && !in_array($userId, $chanelInfo['say_white_list'])){
                    $this->addError('say_white_list', '您当前不再白名单内，不能发言');
                    return false;
                }
                if(is_array($chanelInfo['say_black_list']) && in_array($userId, $chanelInfo['say_black_list'])){
                    $this->addError('say_black_list', '您当前在黑名单中，不能发言');
                    return false;
                }
                return true;
            }else{
                $this->addError('deny_say', "当前组{$chanelId}不允许发言");
                return false;
            }
        }else{
            $this->addError('chanel', "您指定的{$chanelId}不存在");
            return false;
        }
    }
    public function isChanelCanJoin($chanelId, $userInfo){
        $chanel = self::getChanel($chanelId);
        if($chanel){
            // 检查是否已经加入了
            if($this->checkUserInChanel($userInfo, $chanelId)){
                $this->addError('hasJoin', "您已经在频道{$chanelId}中");
                return false;
            }
            // 频道别的检查
            if($chanel['allow_join']){
                $whiteList = $chanel['join_white_list'];
                $blackList = $chanel['join_black_list'];
                if(is_array($blackList) && in_array($userInfo['user_id'], $blackList) ){
                    $this->addError('blacklist', '您当前在黑名单中，禁止加入');
                    return false;
                }
                if(is_array($whiteList) && !in_array($userInfo['user_id'], $whiteList)){
                    $this->addError('whitelist','您当前不在白名单中，静止加入');
                    return false;
                }
                return true;
            }else{
                $this->addError('deny_say', "指定的频道{$chanelId}不允许加入");
                return false;
            }
        }else{
            $this->addError('chanel_id', "指定的chanel ： {$chanelId}不存在");
            return false;
        }
    }
    public function getClientIdInChanel($chanelId, $expect = []){
        $users = Gateway::getClientInfoByGroup($chanelId);
        foreach($expect as $clientId){
            unset($users[$clientId]);
        }
        return array_keys($users);
    }
    public function getChanelUsers($chanelId, $detail = false, $safe = false){
        $users = Gateway::getClientInfoByGroup($chanelId);
        if($detail){
            if($safe){
                foreach($users as $index => $item){
                    $users[$index] = User::getSafeInfo($item);
                }
                return ArrayHelper::index($users, 'user_id');
            }else{
                return $users;
            }
        }else{
            return ArrayHelper::map($users, 'user_id', 'user_id');
        }
        return $users;
    }

    public static function installEventHandler(){
        return [
            self::E_NEW_JOIN_CHANEL => [static::className(), 'eventNewJoinChanel']
        ];
    }
    public static function eventNewJoinChanel($userInfo, $chanelId, $targets, $eventName){
        $res = Res::getEventRes();
        $res['event'] = $eventName;
        $res['body'] = [
            'status' => Res::STATUS_SUCC,
            'data' => [
                'user_info' => $userInfo,
                'chanel_id' => $chanelId,
            ],
            'message' => null,
        ];
        return Res::sendToClients($targets, $res);
    }
}
