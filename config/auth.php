<?php

return [
    'defaults' => [
        'guard' => 'token',
    ],

    'guards' => [
        'repo' => ['driver' => 'repo'],
        'token' => ['driver' => 'token'],
    ]
];
