<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use RepoRangler\Entity\RepositoryUser;
use RepoRangler\Entity\PublicUser;
use RepoRangler\Entity\RestUser;
use RepoRangler\Services\AuthClient;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        Auth::viaRequest('repo', function (Request $request) {
            $authClient = app(AuthClient::class);
            $authHeader = $request->header('Authorization');

            if (empty($authHeader)) {
                return new PublicUser();
            }

            try {
                $response = $authClient->check($authHeader);
                return new \RepoRangler\Entity\User($response);
            } catch (\Exception $e) {
                return new PublicUser();
            }
        });
    }
}
