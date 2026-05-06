<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyEntry;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class DailyEntriesController extends Controller
{
    public function index(Request $request)
    {
        $userId = DemoUser::id();
        $q = DailyEntry::query()->where('user_id', $userId);

        if ($request->filled('year')) $q->where('year', (int) $request->query('year'));
        if ($request->filled('month')) $q->where('month', (string) $request->query('month'));
        if ($request->filled('platform')) $q->where('platform', (string) $request->query('platform'));
        if ($request->filled('account')) $q->where('account', (string) $request->query('account'));

        return response()->json($q->orderByDesc('date')->get());
    }

    public function store(Request $request)
    {
        $userId = DemoUser::id();
        $data = $request->validate([
            'clientId' => ['required', 'string', 'max:64'],
            'date' => ['nullable', 'date'],
            'month' => ['required', 'string', 'max:20'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'platform' => ['required', 'string', 'max:120'],
            'account' => ['required', 'string', 'max:120'],
            'income' => ['nullable', 'numeric'],
            'gmv' => ['nullable', 'numeric'],
            'videos' => ['nullable', 'integer', 'min:0'],
            'itemsSold' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $row = DailyEntry::query()->updateOrCreate(
            ['user_id' => $userId, 'client_id' => $data['clientId']],
            [
                'date' => $data['date'] ?? null,
                'month' => $data['month'],
                'year' => (int) $data['year'],
                'platform' => $data['platform'],
                'account' => $data['account'],
                'income' => $data['income'] ?? 0,
                'gmv' => $data['gmv'] ?? 0,
                'videos' => $data['videos'] ?? 0,
                'items_sold' => $data['itemsSold'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json($row, 201);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $clientId = (string) $request->query('clientId', '');
        if (!$clientId) return response()->json(['error' => 'clientId is required'], 400);

        $row = DailyEntry::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();
        $row->delete();

        return response()->noContent();
    }
}
