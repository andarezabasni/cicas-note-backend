<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\search;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('users');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(10);
        $messages = '';

        if ($users->isEmpty()) {
            $messages = 'Data tidak ditemukan';
        }

        return view('pages.users.index', compact('users'));
    }

    public function create()
    {
        return view('pages.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        User::create($data);
        return redirect()->route('users.index')->with('success', "User successfully created");
    }

    public function show()
    {
        return view('pages.users.show');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('pages.users.edit', compact(['user']));
    }

    public function update(Request $request, $id)
    {
        //validasi
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required|in:admin,staff,user',
        ]);

        //update request
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->save();

        //cek password
        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return redirect()->route('users.index')->with('success', 'User updated succesfully');
    }

    public function destroy(User $user)
    {
        $UserName = $user->name;
        $user->delete();
        return redirect()->route('users.index')->with('success', "User {$UserName} successfully deleted");
    }
}
