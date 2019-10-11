<?php

$required = [];
foreach(['APP_NAME', 'APP_PROTOCOL', 'APP_DOMAIN'] as $key){
    $value = env($key);
    if($value === null) throw new Exception("The env-var '$key' cannot be empty'");
    $required[$key] = $value;
}

return [
    "repo_name" => "Reporangler NPM Repository",
    "repo_desc" => "The NPM repository configuration",
    'repo_type' => $required['APP_NAME'],

    'debug' => env('APP_DEBUG', false),
    'protocol' => $required['APP_PROTOCOL'],
    'domain' => env('APP_DOMAIN', $required['APP_DOMAIN']),

    'npm_base_url'      => env('APP_NPM_URL',   "{$required['APP_PROTOCOL']}://npm.{$required['APP_DOMAIN']}"),
    'php_base_url'      => env('APP_PHP_URL',   "{$required['APP_PROTOCOL']}://php.{$required['APP_DOMAIN']}"),
    'auth_base_url'     => env('APP_AUTH_URL',  "{$required['APP_PROTOCOL']}://auth.{$required['APP_DOMAIN']}"),
    'metadata_base_url' => env('APP_AUTH_URL',  "{$required['APP_PROTOCOL']}://metadata.{$required['APP_DOMAIN']}"),
];
