<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalIncome;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class ExternalIncomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = DemoUser::id();
        $q = ExternalIncome::query()->where('user_id', $userId);

        if ($request->filled('year')) $q->where('year', (int) $request->query('year'));
        if ($request->filled('month')) $q->where('month', (string) $request->query('month'));
        if ($request->filled('source')) $q->where('source', (string) $request->query('source'));

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
            'source' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $row = ExternalIncome::query()->updateOrCreate(
            ['user_id' => $userId, 'client_id' => $data['clientId']],
            [
                'date' => $data['date'] ?? null,
                'month' => $data['month'],
                'year' => (int) $data['year'],
                'source' => $data['source'],
                'amount' => $data['amount'] ?? 0,
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

        $row = ExternalIncome::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();
        $row->delete();

        return response()->noContent();
    }
}
