<?php
namespace wsserver\filters;

use Yii;
use yii\base\ActionFilter;

/**
 *
 */
class RbacControl extends ActionFilter
{
    public function beforeAction($action){
        $auth = Yii::$app->auth;
        return $auth->rbacCan();
    }
}
