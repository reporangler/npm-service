<?php
namespace App\Services;

class RepositoryService
{
    public function getRegistryConfig(): array
    {
        return [
            'db_name' => config('app.repo_name'),
            'description' => config('app.repo_desc'),
        ];
    }
}
