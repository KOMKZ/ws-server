<?php
return [
    'bootstrap' => ['wsserver'],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'wsserver' => [
            'class' => 'wsserver\server\wsserver',
            'workers' => ['worker', 'gateway', 'register']
        ],
        'req' => [
            'class' => 'wsserver\base\Req'
        ],
        'msg' => [
            'class' => 'wsserver\base\Msg',
        ],
        'user' => [
            'class' => 'wsserver\base\User',
        ],
        'auth' => [
            'class' => 'wsserver\base\Auth',
            'roleAssign' => [
                'admin-role' => ['assignId' => 1, 'summary' => '管理员角色'],
                'normal-role' => ['assignId' => 2, 'summary' => '一般用户角色'],
            ],
            'permissionAssign' => [
                'admin-role' => "*",
                'normal-role' => [
                    'chanel' => ['create-public-chanel'],
                    'user' => "*",
                ],
            ],
        ],
    ],
];
