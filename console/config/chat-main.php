<?php
return [
    'bootstrap' => ['cchat'],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'cchat' => [
            'class' => 'cchat\server\CChat',
            'workers' => ['worker', 'gateway', 'register']
        ],
        'req' => [
            'class' => 'cchat\base\Req'
        ],
        'msg' => [
            'class' => 'cchat\base\Msg',
        ],
        'user' => [
            'class' => 'cchat\base\User',
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
                    'chanel' => ['create-public-chanel'],
                    'user' => "*",
                ],
            ],
        ],
    ],
];
