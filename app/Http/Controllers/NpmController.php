<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use RepoRangler\Services\MetadataClient;

class NpmController extends BaseController
{
    public function getPackage(Request $request, string $package)
    {
        $metadata = app(MetadataClient::class);
        $repoType = config('app.repo_type');

        try {
            $packages = $metadata->getPackages($repoType);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        // Find the requested package in the metadata
        $found = null;
        if (isset($packages['data'])) {
            foreach ($packages['data'] as $pkg) {
                if ($pkg['name'] === $package) {
                    $found = $pkg;
                    break;
                }
            }
        }

        if (!$found) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        // Format as NPM registry response
        $versions = [];
        $definition = $found['definition'] ?? [];

        return new JsonResponse([
            'name' => $package,
            'versions' => $definition,
            'dist-tags' => [
                'latest' => $found['version'] ?? '0.0.0',
            ],
        ]);
    }

    public function publish(Request $request, string $package)
    {
        $user = Auth::guard('token')->user();

        if (!$user || $user->is_public_user) {
            return new JsonResponse(['error' => 'unauthorized'], 401);
        }

        $metadata = app(MetadataClient::class);
        $repoType = config('app.repo_type');

        $body = $request->all();
        $name = $body['name'] ?? $package;
        $versions = $body['versions'] ?? [];
        $packageGroup = $request->header('x-package-group', 'public');

        $published = [];

        foreach ($versions as $version => $definition) {
            try {
                $metadata->addPackage($repoType, $packageGroup, $name, $version, $definition);
                $published[] = $version;
            } catch (\Exception $e) {
                return new JsonResponse([
                    'error' => 'publish_failed',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        return new JsonResponse([
            'ok' => true,
            'name' => $name,
            'versions' => $published,
        ]);
    }

    public function listAll(Request $request)
    {
        $metadata = app(MetadataClient::class);
        $repoType = config('app.repo_type');

        try {
            $packages = $metadata->getPackages($repoType);
        } catch (\Exception $e) {
            return new JsonResponse([]);
        }

        return new JsonResponse($packages);
    }
}
