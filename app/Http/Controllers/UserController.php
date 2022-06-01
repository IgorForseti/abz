<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('position')->orderBy('id','ASC')->paginate('6');
        return view('users.index',compact('users'));
    }

    public function getUsers(Request $request)
    {
        $count  = $request->count ?? 5;
        $page   = $request->page ?? 1;
        $offset = $request->offset ?? 0;

        $validator = Validator::make([
            'page' => $page,
            'count' => $count,
            'offset' => $offset,
        ], [
            'page' => 'integer|min:1',
            'count' => 'integer|min:1|max:100',
            'offset' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'fails' => [
                    $validator->errors()
                ]
            ],422);
        }



        $users = User::orderBy('id','ASC')->limit($count)->offset($offset)->get();

        if ($users->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ],404);
        }

        foreach ($users as $key=>$q) {
            $data[$key]['id'] = $q['id'];
            $data[$key]['name'] = $q['name'];
            $data[$key]['email'] = $q['email'];
            $data[$key]['phone'] = $q['phone'];
            $data[$key]['position'] = $q->position->name;
            $data[$key]['position_id'] = $q['position_id'];
            $data[$key]['registration_timestamp'] = $q['registration_timestamp'];
            $data[$key]['photo'] = $q['photo'];
        }

        if ($request->offset) {
            $users = User::orderBy('id','ASC')->limit($count)->offset($offset)->paginate($count);
            $links = [
                'next_url' => $offset + $count >= $users->total() ? null
                    : url('/api/v1/users?offset=' . $offset + $count .'&count=' . $count),
                'prev_url' => $offset - $count < 0 ? null
                    : url('api/v1/users?offset=' . $offset - $count .'&count=' . $count),
            ];
        } else {
            $users = User::orderBy('id','ASC')->paginate($count);
            $links = [
                'next_url' => $users->total() <= $request->page ? null
                    : url('api/v1/users?page=' . ($request->page + 1).'&count=' . $count),
                'prev_url' => $request->page == 1 ? null
                    : url('api/v1/users?page=' . ($request->page - 1).'&count=' . $count)
            ];
        }

        $data = [
                'success' => true,
                'total_pages' => $users->lastPage(),
                'total_users' => $users->total(),
                'count' => $count,
                'links' => $links,
                'users' => $data
        ];

        if ($offset == 0) {
            $data = ['page' => $page] + $data;
        }

        return response()->json($data);
    }

    public function getUser($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'int',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'fails' => [
                    'user_id' => ["The user_id must be an integer."]
                ]
            ],422);
        }

        try {
            $user = User::findOrFail($id)->first();
            return response()->json([
                'success' => true,
                'user' => [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "position" => $user->position->name,
                    "position_id" => $user->position_id,
                    "photo" => $user->photo
                ]]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'The user with the requested identifier does not exist',
                'fails' => [
                    'user_id' => ["User not found"]
                ]
            ],404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position_id' => $request->position_id,
            'photo' => $request->photo,
            'registration_timestamp' => $request->registration_timestamp,
        ], [
            'name' => 'required|min:2|max:60',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users|starts_with:+380|size:13',
            'position_id' => 'required',
            'photo' => 'required|image|max:5120|mimes:jpg,jpeg',
            'registration_timestamp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'fails' => $validator->errors()
            ],422);
        }

        try {
            \Tinify\setKey("8wsSfVCrFNt8VCX2mVZ37XHJLMBs6671");

            $image_name = uniqid();
            $upload_file = public_path("\image\logo\\" . $image_name);
            $path_info = pathinfo($request->file('photo')->getClientOriginalName());
            $source = \Tinify\fromFile($request->file('photo')->getRealPath());
            $resized = $source->resize(array(
                "method" => "thumb",
                "width" => 70,
                "height" => 70
            ));
            $resized->toFile($upload_file . '_opt.' . $path_info['extension']);
        } catch (\Tinify\Exception $e) {

        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position_id' => $request->position_id,
            'photo' => $image_name . '_opt.' . $path_info['extension'],
            'registration_timestamp' => $request->registration_timestamp
        ]);
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'message' => "New user successfully registered"
        ]);
    }
}
