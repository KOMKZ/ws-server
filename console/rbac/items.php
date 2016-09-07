<?php
return [
    'admin-role' => [
        'type' => 1,
        'description' => '管理员角色',
        'children' => [
            'chanel:set-deny-say',
            'chanel:set-allow-join',
            'chanel:append-to-say-black-list',
            'chanel:remove-from-say-black-list',
            'chanel:append-to-join-black-list',
            'chanel:remove-from-join-black-list',
            'chanel:update-chanel',
            'chanel:create-public-chanel',
            'chanel:join-public-chanel',
            'chat:say-in-chanel',
            'user:get-own-info',
        ],
    ],
    'normal-role' => [
        'type' => 1,
        'description' => '一般用户角色',
        'children' => [
            'chanel:join-public-chanel',
            'user:get-own-info',
        ],
    ],
    'chanel:set-deny-say' => [
        'type' => 2,
        'description' => '设置某个频道禁止发言',
    ],
    'chanel:set-allow-join' => [
        'type' => 2,
        'description' => '设置某个频道禁止发言',
    ],
    'chanel:append-to-say-black-list' => [
        'type' => 2,
        'description' => '将某个用户id加入到发言名单当中',
    ],
    'chanel:remove-from-say-black-list' => [
        'type' => 2,
        'description' => '将某个用户id加入到发言名单当中',
    ],
    'chanel:append-to-join-black-list' => [
        'type' => 2,
        'description' => '将某个用户id加入到加入名单当中',
    ],
    'chanel:remove-from-join-black-list' => [
        'type' => 2,
        'description' => '将某个用户id加入到加入名单当中',
    ],
    'chanel:update-chanel' => [
        'type' => 2,
        'description' => '更新频道',
    ],
    'chanel:create-public-chanel' => [
        'type' => 2,
        'description' => '创建一个公共频道',
        'ruleName' => 'chanelRule',
    ],
    'chanel:join-public-chanel' => [
        'type' => 2,
        'description' => '加入一个公共频道',
    ],
    'chat:say-in-chanel' => [
        'type' => 2,
        'description' => '在房间中发言',
    ],
    'user:get-own-info' => [
        'type' => 2,
        'description' => '用户获取自己的信息',
    ],
];
