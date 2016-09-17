<?php
namespace common\models;

use wsserver\base\Model;
use common\base\AppEvent;
use common\base\Res;
use wsserver\base\IdentityInterface;

/**
 *
 */
class UserModel extends Model implements IdentityInterface
{
    public static function getIdentity(){
        return [];
    }
}
