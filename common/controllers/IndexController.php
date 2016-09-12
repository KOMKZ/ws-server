<?php
namespace common\controllers;

use wsserver\base\Controller;
use common\models\UserModel;

/**
 *
 */
class IndexController extends Controller
{
    public function actionLogin(){
        $model = new UserModel();
        // return the response for client callback
        if($model->auth() && $model->login()){
            return $this->success();
        }else{
            $this->error($model->getErrors());
        }
    }
}
