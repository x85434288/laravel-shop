<?php

return [
    'alipay' => [
        'app_id' => '你在支付宝沙箱看到的appid',
        'ali_public_key' => '支付宝沙箱显示的公钥',
        'private_key' => '刚刚生成的私钥',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];