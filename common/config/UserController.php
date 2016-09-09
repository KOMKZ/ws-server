<?php
namespace common\controllers;

use Yii;
use wsserver\base\Res;
use wsserver\base\Controller;
use common\models\User;
use wsserver\filters\AccessControl;


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
    /**
     * 登录操作
     * @return [type] [description]
     */
    public function actionLogin(){
        $data = Yii::$app->req->params;
        $user = new User();
        if(!Yii::$app->user->isGuest){
            $this->response(Res::STATUS_ERR, [['请不要重复登录']]);
        }
        if(true === $user->login()){
            $this->response(Res::STATUS_SUCC, $user->getOwnInfo());
        }else{
            $this->response(Res::STATUS_ERR, $user->getErrors());
        }
    }
    /**
     * 注销操作
     * @return [type] [description]
     */
    public function actionLogout(){
        $this->say();
    }
}
