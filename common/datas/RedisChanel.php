<?php
namespace common\datas;

use Yii;
use yii\base\Object;
use wsserver\redis\ActiveRecord;
/**
 *
 */
class RedisChanel extends ActiveRecord
{
    public function attributes(){
        return [
            'chanel_id',
            'chanel_name',
            'chanel_intro',
            'max_num',
            'allow_join',
            'join_white_list',
            'join_black_list',
            'admin_user_id',
            'deny_say',
            'say_white_list',
            'say_black_list',
            'created_at',
            'updated_at',
            'say_interval',
            'extra'
        ];
    }
    public static function getPkStartValue(){
        $map = [
            'chanel_id' => 1000,
        ];
        return $map;
    }
    public static function primaryKey()
    {
        return ['chanel_id'];
    }

    public function rules(){
        return [
            ['chanel_name', 'required'],
            ['chanel_name', 'string', 'min' => 3, 'max' => 100],
            ['chanel_intro', 'required'],
            ['chanel_intro', 'string', 'min' => 1],
            ['max_num', 'required'],
            ['max_num', 'integer', 'min' => 2, 'max' => 100],
            ['allow_join', 'required'],
            ['allow_join', 'integer'],
            // ['admin_user_id', 'required'],
            ['deny_say', 'required'],
            ['deny_say', 'integer'],
            ['say_interval', 'integer'],
            [[
                'admin_user_id',
                'join_white_list',
                'join_black_list',
                'say_white_list',
                'say_black_list',
                'updated_at',
                'created_at',
                'extra',
            ], 'safe']
        ];
    }
}
