<?php
/**
 * Created by PhpStorm.
 * User: wamil
 * Date: 15-Oct-17
 * Time: 00:40
 */

namespace App\Http\Controllers;

use Auth;
use App\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    public function add(Request $request)
    {

        if ($request->hasFile('image') && $request->has('title'))
        {
            if ($request->file('image')->isValid())
            {
                $image = $request->file('image');

                $mimeType = $image->getClientMimeType();
                if (in_array($mimeType, ['image/jpeg', 'image/png']))
                {
                    $unique_id = md5(microtime());
                    $extension = $image->extension();
                    $fileName = sha1(time()) . '.' . $extension;
                    $request->file('image')->move('uploads/images/', $fileName);

                    $note = new Note();
                    $note->unique_id = $unique_id;
                    $note->image_url = $fileName;
                    $note->title = $request->get('title');
                    if ($request->user())
                    {
                        $note->user_id = $request->user()->id;
                    }

                    $note->content = $note->buildText();
                    $note->save();

                    $access_token = (md5(sha1(microtime())));
                    DB::table('notes_users_access')->insert(
                        ['note_id' => $note->id, 'access_token' => $access_token]
                    );
                    $note->access_token = $access_token;

                    $result = ['status' => 200, 'note' => $note];
                }
                else
                {
                    $result = ['status' => 204];
                }

            }
            else
            {
                $result = ['status' => 203];
            }
        }
        else
        {
            $result = ['status' => 205];
        }

        return response($result);
    }

    public function get()
    {
        $notes = Note::where('user_id', Auth::user()->id)->get();
        return response([
            'status' => 200,
            'data' => [
                'notes' => $notes
            ]
        ]);
    }

    public function getById($id)
    {
        return response([
            'status' => 200,
            'data' => [
                'note' => Note::find($id)
            ]
        ]);
    }

    public function getByAccessToken($accessToken)
    {
        return response([
            'status' => 200,
            'data' => [
                'note' => Note::join('notes_users_access', 'notes_users_access.note_id', '=', 'notes.id')
                    ->where('notes_users_access.access_token', $accessToken)->first()
            ]
        ]);
    }

    public function delete($unique_id)
    {
        DB::table('notes')->where('unique_id', $unique_id)->where('user_id', Auth::user()->id)->delete();
        return response(['status' => 200]);
    }

}