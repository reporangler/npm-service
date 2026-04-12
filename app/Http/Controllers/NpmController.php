<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use RepoRangler\Services\MetadataClient;
use RepoRangler\Services\StorageClient;

class NpmController extends BaseController
{
    public function getPackage(Request $request, string $package)
    {
        $package = urldecode($package);
        $metadata = app(MetadataClient::class);
        $repoType = config('app.repo_type');

        $result = $metadata->getPackagesByName($repoType, $package);

        if (empty($result['data'])) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        // Aggregate versions
        $versions = [];
        $latestVersion = '0.0.0';

        foreach ($result['data'] as $pkg) {
            $version = $pkg['version'];
            $definition = $pkg['definition'] ?? [];

            // Ensure dist block is present
            if (!isset($definition['dist']) && !empty($pkg['storage_key'])) {
                $storage = app(StorageClient::class);
                $definition['dist'] = [
                    'tarball' => $this->getTarballUrl($package, $definition['name'] ?? $package, $version),
                ];
            }

            $versions[$version] = $definition;

            // Simple latest: highest version
            if (version_compare($version, $latestVersion, '>')) {
                $latestVersion = $version;
            }
        }

        return new JsonResponse([
            'name' => $package,
            'dist-tags' => [
                'latest' => $latestVersion,
            ],
            'versions' => $versions,
        ]);
    }

    public function publish(Request $request, string $package)
    {
        $package = urldecode($package);
        $user = $request->user();

        if (!$user || $user->is_public_user) {
            return new JsonResponse(['error' => 'unauthorized', 'message' => 'Authentication required'], 401);
        }

        $body = $request->all();
        $name = $body['name'] ?? $package;
        $versions = $body['versions'] ?? [];
        $attachments = $body['_attachments'] ?? [];

        // Determine package group from scope or default
        if (str_starts_with($name, '@')) {
            $parts = explode('/', $name, 2);
            $packageGroup = ltrim($parts[0], '@');
        } else {
            $packageGroup = $request->header('x-package-group', 'public');
        }

        $metadata = app(MetadataClient::class);
        $storage = app(StorageClient::class);
        $repoType = config('app.repo_type');
        $published = [];

        foreach ($versions as $version => $definition) {
            // Find the matching attachment
            $attachmentKey = $name . '-' . $version . '.tgz';
            $tarball = null;

            if (isset($attachments[$attachmentKey])) {
                $tarball = base64_decode($attachments[$attachmentKey]['data']);
            }

            // Compute hashes
            $storageKey = null;
            if ($tarball) {
                $shasum = sha1($tarball);
                $integrity = 'sha512-' . base64_encode(hash('sha512', $tarball, true));

                // Sanitize name for storage key
                $safeName = str_replace(['@', '/'], ['', '-'], $name);
                $storageKey = "npm/$packageGroup/$safeName/$version/$safeName-$version.tgz";

                // Upload to storage
                $storage->upload($storageKey, $tarball);

                // Inject dist into definition
                $definition['dist'] = [
                    'tarball' => $this->getTarballUrl($name, $safeName, $version),
                    'shasum' => $shasum,
                    'integrity' => $integrity,
                ];
            }

            // Ensure name and version are in definition
            $definition['name'] = $name;
            $definition['version'] = $version;

            // Store metadata
            $metadata->addPackage($repoType, $packageGroup, $name, $version, $definition, $storageKey, 'tgz');
            $published[] = $version;
        }

        return new JsonResponse([
            'ok' => true,
            'name' => $name,
            'versions' => $published,
        ]);
    }

    public function downloadTarball(Request $request, string $package, string $filename)
    {
        $package = urldecode($package);
        $storage = app(StorageClient::class);
        $metadata = app(MetadataClient::class);
        $repoType = config('app.repo_type');

        // Find the package version that matches this filename
        $result = $metadata->getPackagesByName($repoType, $package);

        foreach (($result['data'] ?? []) as $pkg) {
            if (!empty($pkg['storage_key'])) {
                $expectedFilename = basename($pkg['storage_key']);
                if ($expectedFilename === $filename) {
                    $content = $storage->download($pkg['storage_key']);
                    if ($content !== null) {
                        return new Response($content, 200, [
                            'Content-Type' => 'application/octet-stream',
                            'Content-Length' => strlen($content),
                        ]);
                    }
                }
            }
        }

        return new JsonResponse(['error' => 'not_found'], 404);
    }

    public function listAll(Request $request)
    {
        $metadata = app(MetadataClient::class);
        $repoType = config('app.repo_type');

        $result = $metadata->getPackages($repoType);

        // Group by package name and return unique names
        $packages = [];
        foreach (($result['data'] ?? []) as $pkg) {
            $name = $pkg['name'];
            if (!isset($packages[$name])) {
                $packages[$name] = [
                    'name' => $name,
                    'description' => $pkg['definition']['description'] ?? '',
                ];
            }
        }

        return new JsonResponse(array_values($packages));
    }

    public function auditStub(Request $request)
    {
        return new JsonResponse(['actions' => [], 'advisories' => [], 'moreInfoUrl' => '']);
    }

    private function getTarballUrl(string $packageName, string $safeName, string $version): string
    {
        $baseUrl = config('app.npm_base_url');
        $encodedPackage = str_replace('/', '%2f', $packageName);
        return "$baseUrl/$encodedPackage/-/$safeName-$version.tgz";
    }
}
