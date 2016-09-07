<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;


class TestController extends Controller{
    public function actionIndex(){
        $result = Yii::$app->mongodb;
        console($result, '~');
    }
}
