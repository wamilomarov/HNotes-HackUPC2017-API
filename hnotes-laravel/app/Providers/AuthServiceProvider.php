<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        Auth::viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                $api_token = $request->input('api_token');
//                $user = DB::table('api_tokens')
//                    ->where('user_id', $request->input('user_id'))
//                    ->where('api_token', $request->input('api_token'))->limit(1)->get();
                $query = "SELECT u.id FROM users u
                            LEFT JOIN api_tokens at ON at.user_id = u.id
                            WHERE api_token = '$api_token'
                            LIMIT 1";
                $user_id = DB::select($query);
//                var_dump($user_id);
                $user = User::find($user_id[0]->id);
                $user->api_token = $api_token;
                return $user;
//                return User::where('api_token', $request->input('api_token'))->first();
            }
        });
    }
}
