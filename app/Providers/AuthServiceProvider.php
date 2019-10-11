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
//            $authClient = app(AuthClient::class);
//
//            $auth_type = $request->headers->get('php-auth-type', 'http-basic');
//            $auth_user = $request->headers->get('php-auth-user');
//            $auth_password = $request->headers->get('php-auth-pw');
//
//            if(in_array(null, [$auth_user, $auth_password])){
//                return new PublicUser($repository_type);
//            }
//
//            $response = $authClient->login($auth_type, $auth_user, $auth_password);
//
//            return new RepositoryUser($response);
        });
    }
}
