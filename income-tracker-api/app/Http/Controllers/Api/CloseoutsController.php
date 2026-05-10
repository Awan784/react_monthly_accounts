<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Closeout;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class CloseoutsController extends Controller
{
    public function index(Request $request)
    {
        $userId = DemoUser::id();
        $q = Closeout::query()->where('user_id', $userId);

        if ($request->filled('year')) $q->where('year', (int) $request->query('year'));
        if ($request->filled('month')) $q->where('month', (string) $request->query('month'));

        return response()->json($q->orderByDesc('year')->orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'month' => ['required', 'string', 'max:20'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'tiktok' => ['sometimes', 'boolean'],
            'brands' => ['sometimes', 'boolean'],
            'expenses' => ['sometimes', 'boolean'],
            'backup' => ['sometimes', 'boolean'],
        ]);

        $row = Closeout::query()->updateOrCreate(
            ['user_id' => $userId, 'year' => (int) $data['year'], 'month' => $data['month']],
            [
                'tiktok' => (bool) ($data['tiktok'] ?? false),
                'brands' => (bool) ($data['brands'] ?? false),
                'expenses' => (bool) ($data['expenses'] ?? false),
                'backup' => (bool) ($data['backup'] ?? false),
            ]
        );

        return response()->json($row, 201);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $year = (int) $request->query('year');
        $month = (string) $request->query('month', '');
        if (!$year || !$month) return response()->json(['error' => 'year and month are required'], 400);

        $row = Closeout::query()->where('user_id', $userId)->where('year', $year)->where('month', $month)->firstOrFail();
        $row->delete();

        return response()->noContent();
    }
}

