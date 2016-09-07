<?php

namespace common\controllers;

use Yii;
use cchat\base\Controller;
use cchat\filters\AccessControl;
use common\models\Chanel;
use cchat\base\Res;


/**
 * @auth:true
 */
class ChanelController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rbac' => true,
                'rules' => [
                    'actions' => [
                        'create-public-chanel',
                        'join-public-chanel',
                        'set-deny-say',
                        'set-allow-join',
                        'append-to-say-black-list',
                        'remove-from-say-black-list',
                        'append-to-join-black-list',
                        'remove-from-join-black-list',
                        'update-chanel',
                    ],
                ]
            ],
        ];
    }
    /**
     * 获取某个频道中的用户
     * @param  [type] $chanel_id [description]
     * @return [type]            [description]
     */
    public function actionGetChanelUsers($chanel_id){
        $chanelModel = new Chanel();
        $chanel = Chanel::getChanel($chanel_id);
        if($chanel){
            // 详细信息， 安全信息
            $users = $chanelModel->getChanelUsers($chanel_id, true, true);
            return $this->response(Res::STATUS_SUCC, $users);
        }else{
            return $this->response(Res::STATUS_ERR, NULL, [['指定的频道'.$chanel_id.'不存在']]);
        }
    }

    /**
     * 设置某个频道禁止发言
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @return [type]           [description]
     */
    public function actionSetDenySay($chanel_id, $status){
        $chanelModel = new Chanel();
        if($chanelModel->setDenySay($chanel_id, $status)){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(Res::STATUS_ERR, null, $chanelModel->getErrors());
        }
    }

    /**
     * 设置某个频道禁止发言
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @return [type]           [description]
     */
    public function actionSetAllowJoin($chanel_id, $status){
        $chanelModel = new Chanel();
        if($chanelModel->setAllowJoin($chanel_id, $status)){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(Res::STATUS_ERR, null, $chanelModel->getErrors());
        }
    }


    /**
     * 将某个用户id加入到发言名单当中
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @param  [type] $user_id   [description]
     * @return [type]            [description]
     */
    public function actionAppendToSayBlackList($chanel_id, $user_id){
        $chanelModel = new Chanel();
        $result = $chanelModel->appendToSayBlackList($chanel_id, $user_id);
        if(false !== $result){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(null,$chanelModel->getErrors());
        }
    }

    /**
     * 将某个用户id加入到发言名单当中
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @param  [type] $user_id   [description]
     * @return [type]            [description]
     */
    public function actionRemoveFromSayBlackList($chanel_id, $user_id){
        $chanelModel = new Chanel();
        $result = $chanelModel->removeFromSayBlackList($chanel_id, $user_id);
        if(false !== $result){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(null,$chanelModel->getErrors());
        }
    }


    /**
     * 将某个用户id加入到加入名单当中
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @param  [type] $user_id   [description]
     * @return [type]            [description]
     */
    public function actionAppendToJoinBlackList($chanel_id, $user_id){
        $chanelModel = new Chanel();
        $result = $chanelModel->appendToJoinBlackList($chanel_id, $user_id);
        if(false !== $result){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(null,$chanelModel->getErrors());
        }
    }

    /**
     * 将某个用户id加入到加入名单当中
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @param  [type] $user_id   [description]
     * @return [type]            [description]
     */
    public function actionRemoveFromJoinBlackList($chanel_id, $user_id){
        $chanelModel = new Chanel();
        $result = $chanelModel->removeFromJoinBlackList($chanel_id, $user_id);
        if(false !== $result){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(null,$chanelModel->getErrors());
        }
    }



    /**
     * 获取某个频道的信息
     * @param  [type] $chanel_id [description]
     * @return [type]            [description]
     */
    public function actionGetPublicChanel($chanel_id){
        $one = Chanel::getChanel($chanel_id);
        if($one){
            $this->response(Res::STATUS_SUCC, $one);
        }else{
            $this->response(Res::STATUS_ERR, NULL, [['频道不存在']]);
        }
    }

    /**
     * 更新频道
     * @auth:true
     * @param  [type] $chanel_id [description]
     * @param  Array  $data      [description]
     * @return [type]            [description]
     */
    public function actionUpdateChanel($chanel_id, Array $data){
        $chanelModel = new Chanel();
        if($chanelModel->updateChanel($chanel_id, $data)){
            $this->response(Res::STATUS_SUCC, Chanel::getChanel($chanel_id));
        }else{
            $this->response(Res::STATUS_ERR, null, $chanelModel->getErrors());
        }
    }

    /**
     * 创建一个公共频道
     * @auth:true
     * @rule:console\rbac\rules\ChanelRule
     * @return [type] [description]
     */
    public function actionCreatePublicChanel(Array $data)
    {
        $chanelModel = new Chanel();
        if($result = $chanelModel->createChanel($data)){
            $this->response(Res::STATUS_SUCC, $result, '新增成功');
        }else{
            $this->response(Res::STATUS_ERR, null, $chanelModel->getErrors());
        }
    }

    /**
     * 加入一个公共频道
     * @auth:true
     * @return [type] [description]
     */
    public function actionJoinPublicChanel($chanel_id){
        $chanelModel = new Chanel();
        if($chanelModel->joinPublicChanel($chanel_id)){
            $users = $chanelModel->getChanelUsers($chanel_id, true, true);
            $this->response(Res::STATUS_SUCC, $users);
        }else{
            $this->response(Res::STATUS_ERR, null, $chanelModel->getErrors());
        }
    }

}
