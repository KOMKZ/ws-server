<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=common-chat',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'wsserver' => [
            'class' => 'wsserver\server\WsServer'
        ],
        'event' => [
            'class' => 'wsserver\base\Event',
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
            'class' => 'wsserver\base\Req'
        ],
        'msg' => [
            'class' => 'wsserver\base\Msg',
        ],
        'user' => [
            'class' => 'wsserver\base\User',
            'identityClass' => 'common\models\User'
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
                    'chanel' => [
                        'join-public-chanel',
                    ],
                    'user' => "*",
                ],
            ],
        ],
    ]
];
