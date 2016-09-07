<?php
namespace common\controllers;

use Yii;
use cchat\base\Controller;


/**
 *
 */
class PongController extends Controller
{
    public function actionIndex(){
        $this->say();
    }
}
