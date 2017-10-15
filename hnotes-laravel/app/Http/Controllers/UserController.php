<?php
/**
 * Created by PhpStorm.
 * User: wamil
 * Date: 14-Oct-17
 * Time: 23:59
 */

namespace App\Http\Controllers;

use App\Note;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        
    }

    public function register(Request $request)
    {
        if ($request->has('email') && $request->has('password') && $request->has('full_name'))
        {
            $user = new User();
            $user->email = $request->get('email');
            $user->full_name = $request->get('full_name');
            $user->password =  md5($request->get('password'));

            $user->save();

            $token = md5(microtime());

            DB::table('api_tokens')->insert(
                ['user_id' => $user->id, 'api_token' => $token]
            );

            $user->api_token = $token;

            $result = ['status' => 200, 'data' => ['user' => $user]];
            return (new Response($result));
        }
        else
        {
            return response(['status' => 203]);
        }
    }

    public function facebookLogin(Request $request)
    {
        if (!$request->has('access_token'))
        {
            $request = json_decode($request->getContent());
        }

        $user_details = "https://graph.facebook.com/me?access_token=" .$request->access_token;

        $response = file_get_contents($user_details);
        $fb_user = json_decode($response);

        $user = User::where('facebook_id', $fb_user->id)->first();

        if (!$user)
        {
            $user = new User();
            $user->facebook_id = $fb_user->id;
            $user->full_name = $fb_user->name;
            $user->save();

        }

//        var_dump($request->get('access_token'));


        $token = md5(microtime());

        DB::table('api_tokens')->insert(
            ['user_id' => $user->id, 'api_token' => $token]
        );

        $user->api_token = $token;

        return response(['status' => 200, 'data' => ['user' => $user]]);

    }

    public function shareNote(Request $request)
    {
        $note = Note::where('id', $request->get('note_id'))->where('user_id', Auth::user()->id)->get();
        $access_token = (md5(sha1(microtime())));
        DB::table('notes_users_access')->insert(
            ['note_id' => $note[0]->id, 'access_token' => $access_token]
        );

        return response([
            'status' => 200,
            'data' => [
                'url' => "http://www.hnotes.org/api/notes/shared/$access_token",
            ]]);
    }

    public function ownNotes()
    {
        
    }

}