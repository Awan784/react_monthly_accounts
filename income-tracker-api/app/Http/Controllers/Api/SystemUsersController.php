<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SystemUsersController extends Controller
{
    private function assertAdmin(Request $request): void
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || ! (bool) ($user->is_admin ?? false)) {
            abort(403, 'Admin access required.');
        }
    }

    public function index(Request $request)
    {
        $this->assertAdmin($request);

        return response()->json(
            User::query()
                ->orderBy('id')
                ->get(['id', 'name', 'email', 'is_admin', 'created_at'])
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'isAdmin' => (bool) ($u->is_admin ?? false),
                    'createdAt' => optional($u->created_at)->toISOString(),
                ])
        );
    }

    public function store(Request $request)
    {
        $this->assertAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'isAdmin' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => (bool) ($data['isAdmin'] ?? false),
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'isAdmin' => (bool) ($user->is_admin ?? false),
            'createdAt' => optional($user->created_at)->toISOString(),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $this->assertAdmin($request);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'isAdmin' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('name', $data)) $user->name = $data['name'];
        if (array_key_exists('email', $data)) $user->email = $data['email'];
        if (array_key_exists('isAdmin', $data)) $user->is_admin = (bool) $data['isAdmin'];
        if (array_key_exists('password', $data) && $data['password']) $user->password = Hash::make($data['password']);

        $user->save();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'isAdmin' => (bool) ($user->is_admin ?? false),
            'createdAt' => optional($user->created_at)->toISOString(),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->assertAdmin($request);

        /** @var User $actor */
        $actor = $request->user();
        if ((int) $actor->id === (int) $user->id) {
            abort(422, 'You cannot delete your own account.');
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}

