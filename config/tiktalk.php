<?php

return [

    'servers' => [
        1 => ['id' => 1, 'code' => 'prod', 'url' => 'http://tiktalk-av.com'],
        2 => ['id' => 2, 'code' => 'dev', 'url' => 'http://35.229.111.226'],
    ],

    'device_codes' => ['imei', 'android', 'mac', 'firebase', 'unity'],

    'games' => [
        1 => ['id' => 1, 'code' => 'ho', 'name' => 'Hidden Objects'],
        2 => ['id' => 2, 'code' => 'runner', 'name' => 'Runner'],
    ],

    'results' => [
        1 => ['id' => 1, 'code' => 'good', 'name' => 'Good pronunciation (green)'],
        3 => ['id' => 3, 'code' => 'poor', 'name' => 'Poor pronunciation (red)'],
        2 => ['id' => 2, 'code' => 'intermediate', 'name' => 'Intermediate pronunciation (yellow)'],
    ],

    'image_suffixes' => [
        '', '_help', '_sel', '_speech'
    ]

];