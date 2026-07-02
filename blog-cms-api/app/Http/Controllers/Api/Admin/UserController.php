<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()

            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            })

            ->latest()

            ->paginate(10);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:admin,author,user',
        ]);

        if (
            $request->user()->id === $user->id &&
            $data['role'] !== 'admin'
        ) {
            return response()->json([
                'message' => 'You cannot change your own admin role.'
            ], 422);
        }

        $user->update([
            'role' => $data['role'],
        ]);

        return response()->json([
            'message' => 'Role updated successfully.',
            'user' => $user,
        ]);
    }
}
