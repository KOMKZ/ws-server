<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use cchat\base\Worker;

/**
 * 聊天服务
 */
class ChatController extends Controller
{
    /**
     * 守护进程化
     * @var boolean 守护进程化
     */
    public $d = false;
    /**
     * 需要启动的worker实例
     * @var array 需要启动的worker实例
     */
    public $workers = null;

    public $regaddr = null;

    public $lanip = null;

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['d', 'workers', 'regaddr', 'lanip']
        );
    }

    public function init(){

    }


    /**
     * 启动聊天服务
     * @return
     */
    public function actionStart(){
        if($this->regaddr){
            Yii::$app->cchat->workerConfig = ['registerAddress' => $this->regaddr];
            Yii::$app->cchat->gatewayConfig = ['registerAddress' => $this->regaddr];
            Yii::$app->cchat->registerConfig = ['address' => $this->regaddr];
        }
        if($this->lanip){
            Yii::$app->cchat->gatewayConfig = ['lanIp' => $this->lanip];
        }
        null === $this->workers ? '' : Yii::$app->cchat->workers = $this->workers;
        Yii::$app->cchat->run($this->action->id, $this->d);
    }
    /**
     * 停止聊天服务
     * @return
     */
    public function actionStop(){
        Yii::$app->cchat->run($this->action->id, $this->d);
    }
    /**
     * 重启聊天服务
     * @return
     */
    public function actionRestart(){
        if($this->regaddr){
            Yii::$app->cchat->workerConfig = ['registerAddress' => $this->regaddr];
            Yii::$app->cchat->gatewayConfig = ['registerAddress' => $this->regaddr];
            Yii::$app->cchat->registerConfig = ['address' => $this->regaddr];
        }
        if($this->lanip){
            Yii::$app->cchat->gatewayConfig = ['lanIp' => $this->lanip];
        }
        null === $this->workers ? '' : Yii::$app->cchat->workers = $this->workers;
        Yii::$app->cchat->run($this->action->id, $this->d);
    }
    /**
     * 重新加载聊天服务
     * @return
     */
    public function actionReload(){
        if($this->regaddr){
            Yii::$app->cchat->workerConfig = ['registerAddress' => $this->regaddr];
            Yii::$app->cchat->gatewayConfig = ['registerAddress' => $this->regaddr];
            Yii::$app->cchat->registerConfig = ['address' => $this->regaddr];
        }
        if($this->lanip){
            Yii::$app->cchat->gatewayConfig = ['lanIp' => $this->lanip];
        }
        null === $this->workers ? '' : Yii::$app->cchat->workers = $this->workers;
        Yii::$app->cchat->run($this->action->id, $this->d);
    }
    /**
     * 查看了聊天服务的状态
     * @return
     */
    public function actionStatus(){
        Yii::$app->cchat->run($this->action->id, $this->d);
    }
    /**
     * 强制退出聊天服务
     * @return
     */
    public function actionKill(){
        Yii::$app->cchat->run($this->action->id, $this->d);
    }
}
