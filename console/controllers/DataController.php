<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\rbac\PhpManager;
use common\datas\RedisChanel;

/**
 * 初始化数据
 */
class DataController extends Controller{
    public function actionInit(){
        Yii::$app->redis->executeCommand('flushall');
        $this->actionInitChanels();

    }
    public function actionInitChanels(){
        $chanelDataFile = Yii::getAlias('@console/fixture/chanel.php');
        if(is_file($chanelDataFile)){
            $chanelData = include($chanelDataFile);
            foreach($chanelData as $data){
                $chanel = new RedisChanel();
                if($chanel->load($data, '') && $chanel->save()){
                    printf("插入频道成功：%s\n", $data['chanel_name']);
                }else{
                    printf("插入失败\n");
                    print_r($chanel->getErrors());
                }
            }
        }else{
            printf("%s不存在\n", $chanelDataFile);
        }
    }
}
