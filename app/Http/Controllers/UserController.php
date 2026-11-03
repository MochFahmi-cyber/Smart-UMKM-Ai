<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('store_id', auth()->user()->store_id)
            ->when($request->q, function ($query, $q) {
            $query->where(function($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                         ->orWhere('email', 'like', "%{$q}%");
            });
        })
        ->when($request->role, function($query, $role) {
            $query->where('role', $role);
        })
        ->orderBy($request->sort === 'newest' ? 'created_at' : 'name', $request->sort === 'newest' ? 'desc' : 'asc')
        ->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['store_id'] = auth()->user()->store_id;

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        abort_if($user->store_id !== auth()->user()->store_id, 403);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_if($user->store_id !== auth()->user()->store_id, 403);
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_if($user->store_id !== auth()->user()->store_id, 403);
        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->store_id !== auth()->user()->store_id, 403);
        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
