<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::when(request()->q, function($users) {
            $users = $users->where('name', 'LIKE', '%' . request()->q . '%');
        })
        ->where('email', 'NOT LIKE', '%admin@testemail.com%')
        ->latest()
        ->paginate(5);

        return new UserResource(true, 'List Data Users', $users);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        if($user) {
            return new UserResource(true, 'Data User Berhasil Ditambahkan!', $user);
        }

        return new UserResource(false, 'Data User Gagal Ditambahkan!', null);
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $user = User::whereId($id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Detail Data User Tidak Ditemukan!',
            ], 404);
        }

        return new UserResource(true, 'Detail Data User', $user);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
        
            $user->update($data);

            if ($user) {
                return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
            }

            return new UserResource(false, 'Data User Gagal Diupdate!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data User Tidak Ditemukan!',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if($user->delete()) {
                //return success with Api Resource
                return new UserResource(true, 'Data User Berhasil Dihapus!', null);
            }
    
            //return failed with Api Resource
            return new UserResource(false, 'Data User Gagal Dihapus!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data User Tidak Ditemukan!',
            ], 404);
        }
    }
}
