<?php
return [
    'bootstrap' => ['cchat', 'event'],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'cchat' => [
            'class' => 'cchat\server\CChat',
            'workers' => ['worker', 'gateway', 'register'],
            'closeNotLogin' => false,
            'ifClientNoLogin' => ['common\models\User', 'ifClientNoLogin'],
            'loadRemoteConfig' => true,
            'remoteConfigCallback' => ['common\models\App', 'loadRemoteConfig'],
        ],
        'event' => [
            'class' => 'cchat\base\Event',
            'install' => [
                ['common\models\Chat', 'installEventHandler'],
                ['common\models\Chanel', 'installEventHandler'],
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'req' => [
            'class' => 'cchat\base\Req'
        ],
        'msg' => [
            'class' => 'cchat\base\Msg',
        ],
        'user' => [
            'class' => 'cchat\base\User',
            'identityClass' => 'common\models\User'
        ],
        'auth' => [
            'class' => 'cchat\base\Auth',
            'roleAssign' => [
                'admin-role' => ['assignId' => 1, 'summary' => '管理员角色'],
                'normal-role' => ['assignId' => 2, 'summary' => '一般用户角色'],
            ],
            'permissionAssign' => [
                'admin-role' => "*",
                'normal-role' => [
                    'chanel' => [
                        'join-public-chanel',
                    ],
                    'user' => "*",
                ],
            ],
        ],
    ],
];
