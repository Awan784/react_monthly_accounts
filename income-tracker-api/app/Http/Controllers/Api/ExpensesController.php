<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Support\DemoUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpensesController extends Controller
{
    public function index(Request $request)
    {
        $userId = DemoUser::id();
        $q = Expense::query()->where('user_id', $userId);

        if ($request->filled('year')) $q->where('year', (int) $request->query('year'));
        if ($request->filled('month')) $q->where('month', (string) $request->query('month'));
        if ($request->filled('platform')) $q->where('platform', (string) $request->query('platform'));
        if ($request->filled('category')) $q->where('category', (string) $request->query('category'));

        return response()->json($q->orderByDesc('date')->orderByDesc('id')->get()->map(function (Expense $e) {
            return $this->serializeExpense($e);
        }));
    }

    public function store(Request $request)
    {
        $userId = DemoUser::id();

        $data = $request->validate([
            'clientId' => ['required', 'string', 'max:64'],
            'date' => ['nullable', 'date'],
            'month' => ['required', 'string', 'max:20'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'category' => ['required', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'receipt' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,webp,heic,heif,pdf',
            ],
        ]);

        $receiptMeta = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $disk = 'public';
            $path = $file->store('expense-receipts', $disk);

            $receiptMeta = [
                'receipt_disk' => $disk,
                'receipt_path' => $path,
                'receipt_original_name' => $file->getClientOriginalName(),
                'receipt_mime' => $file->getClientMimeType(),
                'receipt_size' => $file->getSize(),
            ];
        }

        $row = Expense::query()->updateOrCreate(
            ['user_id' => $userId, 'client_id' => $data['clientId']],
            array_merge([
                'date' => $data['date'] ?? null,
                'month' => $data['month'],
                'year' => (int) $data['year'],
                'category' => $data['category'],
                'platform' => $data['platform'] ?? null,
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
            ], $receiptMeta ?? [])
        );

        return response()->json($this->serializeExpense($row), 201);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $clientId = (string) $request->query('clientId', '');
        if (!$clientId) return response()->json(['error' => 'clientId is required'], 400);

        $row = Expense::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();

        $this->deleteReceiptIfPresent($row);
        $row->delete();

        return response()->noContent();
    }

    public function receipt(string $clientId)
    {
        $userId = DemoUser::id();

        $row = Expense::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();
        if (!$row->receipt_disk || !$row->receipt_path) {
            return response()->json(['error' => 'receipt not found'], 404);
        }

        $disk = $row->receipt_disk;
        $path = $row->receipt_path;
        if (!Storage::disk($disk)->exists($path)) {
            return response()->json(['error' => 'receipt not found'], 404);
        }

        $downloadName = $row->receipt_original_name ?: basename($path);
        $fullPath = Storage::disk($disk)->path($path);
        $mime = $row->receipt_mime ?: $this->guessReceiptMimeFromName($downloadName);

        return response()->download($fullPath, $downloadName, array_filter([
            'Content-Type' => $mime,
        ]));
    }

    private function serializeExpense(Expense $e): array
    {
        return [
            'id' => $e->id,
            'userId' => $e->user_id,
            'clientId' => $e->client_id,
            'date' => $e->date?->format('Y-m-d'),
            'month' => $e->month,
            'year' => $e->year,
            'category' => $e->category,
            'platform' => $e->platform,
            'amount' => (string) $e->amount,
            'notes' => $e->notes,
            'receipt' => $e->receipt_path ? [
                'name' => $e->receipt_original_name,
                'mime' => $e->receipt_mime,
                'size' => $e->receipt_size,
                'downloadUrl' => url('/api/expenses/'.$e->client_id.'/receipt'),
            ] : null,
        ];
    }

    private function deleteReceiptIfPresent(Expense $e): void
    {
        if (!$e->receipt_disk || !$e->receipt_path) return;

        try {
            Storage::disk($e->receipt_disk)->delete($e->receipt_path);
        } catch (\Throwable $ignored) {
            // best-effort cleanup
        }
    }

    private function guessReceiptMimeFromName(string $name): string
    {
        return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'heic', 'heif' => 'image/heic',
            default => 'application/octet-stream',
        };
    }
}

