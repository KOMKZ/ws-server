<?php
namespace wsserver\base;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 *
 */
class Msg extends Component
{
    public function parseRequest(&$request){
        $sourceData = $request->sourceData;
        $request->clientId = $sourceData['clientId'];
        $data = json_decode($sourceData['body'], true);
        if(null !== $data && is_array($data)){
            if(!array_key_exists('route', $data) || !is_string($data['route'])){
                $data['route'] = null;
            }
            if(!array_key_exists('params', $data)){
                $data['params'] = [];
            }
            $defalutHeader = [
                'cb_index' => null,
            ];

            if(!array_key_exists('header', $data)){
                $data['header'] = [];
            }
            $request->route = $data['route'];
            $request->params = $data['params'];
            $request->header = ArrayHelper::merge($request->header, $data['header']);
            return [$data['route'], $data['params']];
        }else{
            return [null, null];
        }
    }
}
