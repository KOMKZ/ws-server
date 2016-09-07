<?php
namespace common\controllers;

use Yii;
use cchat\base\Res;
use cchat\base\Controller;
use common\models\User;
use cchat\filters\AccessControl;


/**
 * @auth:true
 */
class UserController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    'actions' => ['get-own-info', 'logout'],
                ]
            ],
        ];
    }

    /**
     * 用户获取自己的信息
     * @auth:true
     * @return [type] [description]
     */
    public function actionGetOwnInfo(){
        $ownInfo = User::getOwnInfo();
        $this->response(Res::STATUS_SUCC, $ownInfo);
    }

    public function actionLogout(){
        if(!Yii::$app->user->isGuest){
            $this->response(Res::STATUS_SUCC, null);
            User::logout();
        }else{
            $this->response(Res::STATUS_ERR, null, [['您未登录']]);
        }
    }

    /**
     * 登录操作
     * @return [type] [description]
     */
    public function actionLogin(){
        $data = Yii::$app->req->params;
        $user = new User();
        if(!Yii::$app->user->isGuest){
            $this->response(Res::STATUS_ERR, null, [['请不要重复登录']]);
        }
        if(true === $user->login()){
            $this->response(Res::STATUS_SUCC, $user->getOwnInfo());
        }else{
            $this->response(Res::STATUS_ERR, null, $user->getErrors());
        }
    }

}
