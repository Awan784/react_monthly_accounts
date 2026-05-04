<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function store(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $account = Account::query()->create([
            'user_id' => $userId,
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return response()->json($account, 201);
    }

    public function update(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['sometimes', 'string', 'max:120'],
            'isActive' => ['sometimes', 'boolean'],
        ]);

        $account = Account::query()->where('user_id', $userId)->findOrFail($data['id']);

        if (array_key_exists('name', $data)) $account->name = trim($data['name']);
        if (array_key_exists('isActive', $data)) $account->is_active = (bool) $data['isActive'];

        $account->save();

        return response()->json($account);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $id = (int) $request->query('id');
        if (!$id) return response()->json(['error' => 'id is required'], 400);

        $account = Account::query()->where('user_id', $userId)->findOrFail($id);
        $account->delete();

        return response()->noContent();
    }
}
