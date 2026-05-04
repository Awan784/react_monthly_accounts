<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class PlatformsController extends Controller
{
    public function store(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $platform = Platform::query()->create([
            'user_id' => $userId,
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return response()->json($platform, 201);
    }

    public function update(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['sometimes', 'string', 'max:120'],
            'isActive' => ['sometimes', 'boolean'],
        ]);

        $platform = Platform::query()->where('user_id', $userId)->findOrFail($data['id']);

        if (array_key_exists('name', $data)) $platform->name = trim($data['name']);
        if (array_key_exists('isActive', $data)) $platform->is_active = (bool) $data['isActive'];

        $platform->save();

        return response()->json($platform);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $id = (int) $request->query('id');
        if (!$id) return response()->json(['error' => 'id is required'], 400);

        $platform = Platform::query()->where('user_id', $userId)->findOrFail($id);
        $platform->delete();

        return response()->noContent();
    }
}
