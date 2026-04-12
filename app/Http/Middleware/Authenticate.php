<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use RepoRangler\Entity\PublicUser;
use RepoRangler\Entity\User;
use RepoRangler\Services\AuthClient;

class Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        $guard = $guards[0] ?? 'token';

        if ($guard === 'token' || $guard === 'custom-token') {
            $authHeader = $request->header('Authorization');

            if (empty($authHeader)) {
                throw new AuthenticationException('Unauthenticated.');
            }

            try {
                $authClient = app(AuthClient::class);
                $response = $authClient->check($authHeader);
                $user = new User($response);

                $request->setUserResolver(function () use ($user) {
                    return $user;
                });

                return $next($request);
            } catch (\Throwable $e) {
                throw new AuthenticationException('Unauthenticated.');
            }
        }

        if ($guard === 'repo' || $guard === 'custom-repo') {
            // Try to authenticate, fall back to public user
            $authHeader = $request->header('Authorization');

            if (empty($authHeader)) {
                $user = new PublicUser();
            } else {
                try {
                    $authClient = app(AuthClient::class);
                    $response = $authClient->check($authHeader);
                    $user = new User($response);
                } catch (\Throwable $e) {
                    $user = new PublicUser();
                }
            }

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);
        }

        throw new AuthenticationException('Unauthenticated.');
    }
}
