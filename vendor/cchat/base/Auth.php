<?php
namespace cchat\base;

use Yii;
use yii\base\Component;
/**
 *
 */
class Auth extends Component
{
    const AUTH_BY_RBAC = 'rbac';
    CONST AUTH_BY_TOKEN = 'token';
    public $type = 'rbac';

    public $roleAssign = [];
    public $permissionAssign = [];

    public $role = null;
    public $permission = null;
    public $assignId = null;
    public $params = null;

    public $signature = null;
    public $data = null;


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

    public function rbacCan($data = []){
        $authManager = Yii::$app->authManager;
        // get role if there is
        if(array_key_exists('assignId', $data)){
            $this->assignId = $data['assignId'];
        }elseif($assignId = Yii::$app->user->getAssignId()){
            $this->assignId = $assignId;
        }else{
            return false;
        }
        // get permission if there is
        if(array_key_exists('permission', $data)){
            $this->permission = $data['permission'];
        }elseif(Yii::$app->req->route){
            $this->permission = preg_replace('/\//',':',Yii::$app->req->route);
        }else{
            return false;
        }
        // 说明这个操作不需要权限控制
        if(!$authManager->getPermission($this->permission)){
            return true;
        }
        // get params if there is
        if(array_key_exists('params', $data)){
            $this->params = $data['params'];
        }else{
            $this->params = Yii::$app->req->params;
        }
        return $authManager->checkAccess($this->assignId, $this->permission, $this->params);
    }
    public function tokenCan($data = []){
        if(array_key_exists('data', $data)){
            $this->data = $data['data'];
        }else{
            $this->data = Yii::$app->req->sourceData;
        }

        if(array_key_exists('signature', $data)){
            $this->signature = $data['signature'];
        }else{
            return false;
        }

        return $this->isValidSignature();

    }

    public function isValidSignature(){
        if(!Yii::$app->cchat->secretKey){
            throw new \Exception('secretKey没有设置');
        }
        $jsonData = json_encode($this->data);
        if(!empty($this->jsonData)){
            $signature = sha1(md5($this->jsonData) . Yii::$app->cchat->secretKey);
            return $this->signature === $signature;
        }else{
            return false;
        }
    }







}
