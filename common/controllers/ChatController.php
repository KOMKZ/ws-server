<?php
namespace common\controllers;

use Yii;
use cchat\base\Event;
use cchat\base\Controller;
use common\models\Chat;
use cchat\base\Res;
use GatewayWorker\Lib\Gateway;
use cchat\filters\AccessControl;

/**
 * 聊天逻辑控制器
 * @auth:true
 */
class ChatController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rbac' => true,
                'rules' => [
                    'actions' => [
                        'say-in-chanel'
                    ],
                ]
            ],
        ];
    }
    /**
     * 在房间中发言
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @return [type]            [description]
     */
    public function actionSayInChanel($chanel_id){
        if(!array_key_exists('data', Yii::$app->req->params)){
            return $this->response(Res::STATUS_ERR, NULL, [['你没有发送数据']]);
        }
        $chatModel = new Chat();
        $data = Yii::$app->req->params['data'];
        if($chatModel->sayInChanel($chanel_id, $data)){
            return $this->response(Res::STATUS_SUCC, NULL);
        }else{
            return $this->response(Res::STATUS_ERR, NULL, $chatModel->getErrors());
        }
    }
}
