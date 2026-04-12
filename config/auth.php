<?php

return [
    'defaults' => [
        'guard' => 'token',
    ],

    'guards' => [
        'repo'  => ['driver' => 'custom-repo'],
        'token' => ['driver' => 'custom-token'],
    ]
];
