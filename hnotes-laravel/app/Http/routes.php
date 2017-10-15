<?php
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});


//$app->post('/register', 'UserController@register');

$app->post('/notes', 'NoteController@add');
$app->get('/notes', ['middleware' => 'auth', 'uses' => 'NoteController@get']);
$app->post('/notes/bas64/', 'NoteController@addBase64');
$app->get('/notes/{id}', ['middleware' => 'auth', 'uses' => 'NoteController@getById']);
$app->post('/notes/share', 'UserController@shareNote');
$app->get('/notes/shared/{access_token}', 'NoteController@getByAccessToken');
$app->get('/notes/delete/{unique_id}', 'NoteController@delete');

$app->post('/login/facebook', 'UserController@facebookLogin');
$app->post('/login/google', 'UserController@googleLogin');

