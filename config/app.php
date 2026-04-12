<?php

$required = [];
foreach (['APP_NAME', 'APP_PROTOCOL', 'APP_DOMAIN'] as $key) {
    $value = env($key);
    if ($value === null) throw new Exception("The env-var '$key' cannot be empty'");
    $required[$key] = $value;
}

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel 11 Required Keys
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'npm'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'maintenance' => [
        'driver' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom RepoRangler Keys
    |--------------------------------------------------------------------------
    */
    "repo_name" => "Reporangler NPM Repository",
    "repo_desc" => "The NPM repository configuration",
    'repo_type' => $required['APP_NAME'],

    'protocol' => $required['APP_PROTOCOL'],
    'domain' => env('APP_DOMAIN', $required['APP_DOMAIN']),

    'npm_base_url'      => env('APP_NPM_URL',   "{$required['APP_PROTOCOL']}://npm.{$required['APP_DOMAIN']}"),
    'php_base_url'      => env('APP_PHP_URL',   "{$required['APP_PROTOCOL']}://php.{$required['APP_DOMAIN']}"),
    'auth_base_url'     => env('APP_AUTH_URL',  "{$required['APP_PROTOCOL']}://auth.{$required['APP_DOMAIN']}"),
    'metadata_base_url' => env('APP_METADATA_URL',  "{$required['APP_PROTOCOL']}://metadata.{$required['APP_DOMAIN']}"),
    'storage_base_url'  => env('APP_STORAGE_URL', "{$required['APP_PROTOCOL']}://storage.{$required['APP_DOMAIN']}"),
    'storage_public_url' => env('APP_STORAGE_PUBLIC_URL', env('APP_STORAGE_URL', "{$required['APP_PROTOCOL']}://storage.{$required['APP_DOMAIN']}")),
];
