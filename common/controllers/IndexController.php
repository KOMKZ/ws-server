<?php
namespace common\controllers;

use wsserver\base\Controller;
use common\models\UserModel;
use wsserver\filters\RbacControl;

/**
 *@auth:true
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
    /**
     * @return [type] [description]
     */
    public function actionLogin(){
        console('hello world', '~');
    }
}
