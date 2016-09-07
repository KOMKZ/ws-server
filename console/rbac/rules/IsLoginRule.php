<?php
namespace console\rbac\rules;

use Yii;
use yii\rbac\Rule;

/**
 *
 */
class IsLoginRule extends Rule
{
    public $name = 'isLoginRule';

    public function execute($id, $item, $params)
    {
        return !Yii::$app->user->isGuest;
    }
}
