<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrandDeal;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class BrandDealsController extends Controller
{
    public function index(Request $request)
    {
        $userId = DemoUser::id();
        $q = BrandDeal::query()->where('user_id', $userId);

        if ($request->filled('year')) $q->where('year', (int) $request->query('year'));
        if ($request->filled('month')) $q->where('month', (string) $request->query('month'));
        if ($request->filled('platform')) $q->where('platform', (string) $request->query('platform'));
        if ($request->filled('account')) $q->where('account', (string) $request->query('account'));
        if ($request->filled('status')) $q->where('status', (string) $request->query('status'));

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

            'brand' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'product' => ['nullable', 'string', 'max:255'],

            'amount' => ['nullable', 'numeric'],
            'status' => ['required', 'in:Paid,Pending,Overdue,Follow-Up'],
            'dueDate' => ['nullable', 'date'],
            'usageRights' => ['nullable', 'string', 'max:255'],
            'contract' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $row = BrandDeal::query()->updateOrCreate(
            ['user_id' => $userId, 'client_id' => $data['clientId']],
            [
                'date' => $data['date'] ?? null,
                'month' => $data['month'],
                'year' => (int) $data['year'],
                'platform' => $data['platform'],
                'account' => $data['account'],

                'brand' => $data['brand'] ?? '',
                'contact' => $data['contact'] ?? null,
                'product' => $data['product'] ?? null,

                'amount' => $data['amount'] ?? 0,
                'status' => $data['status'],
                'due_date' => $data['dueDate'] ?? null,
                'usage_rights' => $data['usageRights'] ?? null,
                'contract' => $data['contract'] ?? null,
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

        $row = BrandDeal::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();
        $row->delete();

        return response()->noContent();
    }
}
