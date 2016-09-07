<?php
namespace common\controllers;

use cchat\base\Controller;

/**
 *
 */
class IndexController extends Controller
{
    public function actionIndex(){
        console($this->id, '~');
    }
}
