<?php
namespace common\controllers;

use wsserver\base\Controller;
use common\models\UserModel;
use wsserver\filters\RbacControl;

/**
 *
 */
class IndexController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => RbacControl::className(),
            ],
        ];
    }
    public function actionLogin(){

    }
}
