<?php

use Dtm\Constants\DbType;
use Dtm\Constants\Protocol;

return [
    'dtm'=>[
        'protocol' => Protocol::HTTP,
        'server' => '82.157.58.76',
        'port' => [
            'http' => 36789,
            'grpc' => 36790,
        ],
        'barrier' => [
            'db' => [
                'type' => DbType::MySQL,
            ],
            'apply' => [],
        ],
        'guzzle' => [
            'options' => [],
        ],
    ]
];