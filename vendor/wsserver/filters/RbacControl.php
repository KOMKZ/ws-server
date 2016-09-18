<?php
namespace wsserver\filters;

use Yii;
use yii\base\ActionFilter;
use wsserver\base\Res;
use wsserver\base\Auth;

/**
 *
 */
class RbacControl extends ActionFilter
{
    public function beforeAction($action){
        $auth = Yii::$app->auth;
        if(array_key_exists('auth_type', Yii::$app->req->header) && $auth->isValidType(Yii::$app->req->header['auth_type'])){
            $auth->type = Yii::$app->req->header['auth_type'];
        }else{
            $auth->type = $auth->getDefaultType();
        }
        $can = $auth->can();
        if(!$can){
            $res = Res::getRouteRes();
            $res['route'] = Yii::$app->req->route;
            $res['body'] = [
                'status' => Res::STATUS_ERR,
                'data' => null,
                'message' => [['You have no permission to run the route ' . $res['route']]]
            ];
            Res::sendToCurrent($res);
            return false;
        }
        return true;
    }
}
