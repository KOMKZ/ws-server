<?php
namespace cchat\base;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
/**
 *
 */
class User extends Component
{
    private $_identityClass = null;
    private $_protectedAttr = [];


    private function getRequireAttr(){
        return [
            'user_name' => null,
            'user_id' => null,
            'is_guest' => true,
            'client_id' => null,
            'assign_id' => null,
        ];
    }
    public function getProtectedAttr(){
        return $this->_protectedAttr;
    }
    public function setProtectedAttr($attrs){
        $this->_protectedAttr = $attrs;
    }
    public function getIdentity(){
        $identity = $this->_identityClass;
        $userInfo = ArrayHelper::merge($this->getRequireAttr(), $identity::getIdentity());
        return $userInfo;
    }
    




    public function getIsGuest(){
        $userInfo = $this->getIdentity();
        if(!empty($userInfo) && array_key_exists('is_guest', $userInfo) && (false === $userInfo['is_guest'])){
            return false;
        }
        return true;
    }

    public function getAssignId(){
        return $this->getIdentity()['assign_id'];
    }
    public function getIdentityClass(){
        return $this->_identityClass;
    }
    public function setIdentityClass($class){
        if(class_exists($class)){
            $this->_identityClass = $class;
        }else{
            throw new \Exception("{$class} doesn't exist");
        }
    }


}
