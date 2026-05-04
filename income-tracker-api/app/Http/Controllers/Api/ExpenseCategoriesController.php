<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class ExpenseCategoriesController extends Controller
{
    public function store(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $row = ExpenseCategory::query()->create([
            'user_id' => $userId,
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return response()->json($row, 201);
    }

    public function update(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['sometimes', 'string', 'max:120'],
            'isActive' => ['sometimes', 'boolean'],
        ]);

        $row = ExpenseCategory::query()->where('user_id', $userId)->findOrFail($data['id']);

        if (array_key_exists('name', $data)) $row->name = trim($data['name']);
        if (array_key_exists('isActive', $data)) $row->is_active = (bool) $data['isActive'];

        $row->save();

        return response()->json($row);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $id = (int) $request->query('id');
        if (!$id) return response()->json(['error' => 'id is required'], 400);

        $row = ExpenseCategory::query()->where('user_id', $userId)->findOrFail($id);
        $row->delete();

        return response()->noContent();
    }
}
