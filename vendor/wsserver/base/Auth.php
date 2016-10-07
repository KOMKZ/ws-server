<?php
namespace wsserver\base;

use Yii;
use yii\base\Component;

/**
 * 各种can方式应该独立出去，本类提供add方法，要不台耦合了。
 */
class Auth extends Component
{
    const AUTH_BY_RBAC = 'rbac';
    CONST AUTH_BY_TOKEN = 'token';
    public $type = 'rbac';
    public $defaultAssignId = null;

    public $roleAssign = [];
    public $permissionAssign = [];

    public $role = null;
    public $permission = null;
    public $assignId = null;
    public $params = null;

    public $token = null;
    public $data = null;

    /**
     * 1. 注意执行到这一步说明用户肯定是指定了该方法的，所以方法的名称是必须带过来的。
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function can($data = []){
        switch ($this->type) {
            case self::AUTH_BY_RBAC:
                return $this->rbacCan($data);
                break;
            case self::AUTH_BY_TOKEN:
                return $this->tokenCan($data);
                break;
            default:
                throw new \Exception("不存在auth-type{$this->type}");
                return false;
        }
    }
    public function isValidType($type){
        return in_array($type, [self::AUTH_BY_RBAC, self::AUTH_BY_TOKEN]);
    }
    public function getDefaultType(){
        return self::AUTH_BY_RBAC;
    }
    public function setPermission($data = []){
        // get permission if there is
        if(array_key_exists('permission', $data)){
            $this->permission = $data['permission'];
        }elseif(Yii::$app->req->route){
            $this->permission = preg_replace('/\//', ':',Yii::$app->req->route);
        }else{
            $this->permission = null;
        }
    }
    public function setParams($data = []){
        if(array_key_exists('params', $data)){
            $this->params = $data['params'];
        }else{
            $this->params = Yii::$app->req->params;
        }
    }
    public function rbacCan($data = []){
        $authManager = Yii::$app->authManager;
        $this->setPermission($data);
        /**
         * cant find the route but route is required
         */
        if(!$this->permission){
            return false;
        }
        /**
         * indacate that the route can be accessed public
         */
        if(!$authManager->getPermission($this->permission)){
            return true;
        }

        // get role if there is
        if($assignId = Yii::$app->user->getAssignId()){
            $this->assignId = $assignId;
        }elseif(Yii::$app->user->getIsGuest() && null !== $this->defaultAssignId){
            $this->assignId = $this->defaultAssignId;
        }else{
            // todo log
            return false;
        }
        // get params if there is
        $this->setParams($data);
        return $authManager->checkAccess($this->assignId, $this->permission, $this->params);
    }
    /**
     * 使用分配token给各个应用客户端， 可以对token进行授权
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function tokenCan($data = []){
        $authManager = Yii::$app->authManager;
        $this->setPermission($data);
        if(!$this->permission){
            return false;
        }
        // 说明这个操作不需要权限控制
        if(!$authManager->getPermission($this->permission)){
            return true;
        }

        if(array_key_exists('auth_token', Yii::$app->req->header)){
            $this->token = Yii::$app->req->header['auth_token'];
        }else{
            return false;
        }

        if($assignId = $this->getTokenAssignId($this->token)){
            $this->assignId = $assignId;
        }else{
            return false;
        }
        $this->setParams($data);
        return $authManager->checkAccess($this->assignId, $this->permission, $this->params);
    }

    protected function getTokenAssignId($token){
        return 1;
    }







}
