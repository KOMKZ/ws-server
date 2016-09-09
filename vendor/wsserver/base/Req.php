<?php
namespace wsserver\base;

use Yii;
use yii\base\Component;

/**
 *
 */
class Req extends Component
{
    public $sourceData = null;
    public $route = null;
    public $params = null;
    public $clientId = null;
    public $header = [
        'cb_index' => null,
    ];

    public function resolve($data){
        $this->sourceData = $data;
        $msgManager = Yii::$app->wsserver->getMsgManager();
        $parts = $msgManager->parseRequest($this);
        return false === $parts ? false : $parts;
    }
}
