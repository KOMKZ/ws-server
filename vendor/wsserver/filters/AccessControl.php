<?php
namespace wsserver\filters;

use Yii;
use yii\di\Instance;
use yii\base\ActionFilter;
use wsserver\base\User;
use wsserver\base\Res;


/**
 *
 */
class AccessControl extends ActionFilter
{
    public $rules = [];
    public $rbac = false;
    public function beforeAction($action)
    {
        if(array_key_exists('actions', $this->rules) && is_array($this->rules['actions'])){
            if(in_array($action->id, $this->rules['actions'])){
                if(true === $this->rbac && !Yii::$app->user->getIsGuest()){
                    $authData['permission'] = $action->controller->id . ':' . $action->id;
                    $authData['assignId'] = Yii::$app->user->getAssignId();
                    if(!Yii::$app->auth->rbacCan($authData)){
                        $action->controller->response(Res::STATUS_ERR, null, [['您没有权限使用该操作']]);
                        return false;
                    }
                }else{
                    if(Yii::$app->user->getIsGuest()){
                        $action->controller->response(Res::STATUS_ERR, null, [['该操作必须登录才能使用']]);
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
