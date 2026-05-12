<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrandDeal;
use App\Support\DemoUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandDealsController extends Controller
{
    /** Columns for listings — excludes `contract` JSON (can be huge base64) so ORDER BY does not exhaust sort memory. */
    private const INDEX_COLUMNS = [
        'id',
        'user_id',
        'client_id',
        'date',
        'month',
        'year',
        'platform',
        'account',
        'brand',
        'contact',
        'product',
        'amount',
        'status',
        'due_date',
        'usage_rights',
        'notes',
        'contract_disk',
        'contract_path',
        'contract_original_name',
        'contract_mime',
        'contract_size',
        'contract_legacy_json',
        'created_at',
        'updated_at',
    ];

    public function index(Request $request)
    {
        $userId = DemoUser::id();
        $q = BrandDeal::query()->where('user_id', $userId)->select(self::INDEX_COLUMNS);

        if ($request->filled('year')) {
            $q->where('year', (int) $request->query('year'));
        }
        if ($request->filled('month')) {
            $q->where('month', (string) $request->query('month'));
        }
        if ($request->filled('platform')) {
            $q->where('platform', (string) $request->query('platform'));
        }
        if ($request->filled('account')) {
            $q->where('account', (string) $request->query('account'));
        }
        if ($request->filled('status')) {
            $q->where('status', (string) $request->query('status'));
        }

        return response()->json(
            $q->orderByDesc('date')->get()->map(fn (BrandDeal $row) => $this->serializeBrandDeal($row))
        );
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
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('contract')) {
            $request->validate([
                'contract' => ['file', 'max:25600', 'mimes:pdf,png,jpg,jpeg,webp,doc,docx'],
            ]);
        }

        $existing = BrandDeal::query()->where('user_id', $userId)->where('client_id', $data['clientId'])->first();

        $contractMeta = [];
        if ($request->hasFile('contract')) {
            $this->deleteContractIfPresent($existing);
            $file = $request->file('contract');
            $disk = 'public';
            $path = $file->store('brand-contracts', $disk);
            $contractMeta = [
                'contract_disk' => $disk,
                'contract_path' => $path,
                'contract_original_name' => $file->getClientOriginalName(),
                'contract_mime' => $file->getClientMimeType(),
                'contract_size' => $file->getSize(),
                'contract' => null,
                'contract_legacy_json' => false,
            ];
        } elseif (is_array($request->input('contract')) && ! empty($request->input('contract')['data'])) {
            $this->deleteContractIfPresent($existing);
            $migrated = $this->persistContractFromDataUrl($request->input('contract'));
            if ($migrated) {
                $contractMeta = $migrated;
            }
        }

        $payload = array_merge([
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
            'notes' => $data['notes'] ?? null,
        ], $contractMeta);

        $row = BrandDeal::query()->updateOrCreate(
            ['user_id' => $userId, 'client_id' => $data['clientId']],
            $payload
        );

        return response()->json($this->serializeBrandDeal($row), 201);
    }

    public function contract(string $clientId)
    {
        $userId = DemoUser::id();
        $row = BrandDeal::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();

        if ($row->contract_disk && $row->contract_path) {
            $disk = $row->contract_disk;
            $path = $row->contract_path;
            if (! Storage::disk($disk)->exists($path)) {
                return response()->json(['error' => 'contract file not found'], 404);
            }
            $downloadName = $row->contract_original_name ?: basename($path);
            $fullPath = Storage::disk($disk)->path($path);
            $mime = $row->contract_mime ?: $this->guessMimeFromName($downloadName);

            return response()->download($fullPath, $downloadName, array_filter([
                'Content-Type' => $mime,
            ]));
        }

        $legacy = $row->contract;
        if (is_array($legacy) && ! empty($legacy['data']) && is_string($legacy['data'])) {
            $decoded = $this->decodeDataUrl($legacy['data']);
            if (! $decoded) {
                return response()->json(['error' => 'contract not found'], 404);
            }
            [$binary, $mime] = $decoded;
            $downloadName = $legacy['name'] ?? 'contract';

            return response($binary, 200, array_filter([
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="'.addslashes($downloadName).'"',
            ]));
        }

        return response()->json(['error' => 'contract not found'], 404);
    }

    public function destroy(Request $request)
    {
        $userId = DemoUser::id();
        $clientId = (string) $request->query('clientId', '');
        if (! $clientId) {
            return response()->json(['error' => 'clientId is required'], 400);
        }

        $row = BrandDeal::query()->where('user_id', $userId)->where('client_id', $clientId)->firstOrFail();
        $this->deleteContractIfPresent($row);
        $row->delete();

        return response()->noContent();
    }

    private function serializeBrandDeal(BrandDeal $b): array
    {
        $hasFile = (bool) ($b->contract_disk && $b->contract_path);
        $hasLegacyFlag = (bool) ($b->contract_legacy_json ?? false);
        $hasInlineData = is_array($b->contract) && ! empty($b->contract['data']);
        $hasDownload = $hasFile || $hasLegacyFlag || $hasInlineData;

        $contractName = $b->contract_original_name
            ?? (is_array($b->contract) ? ($b->contract['name'] ?? null) : null)
            ?? 'contract';
        $contractMime = $b->contract_mime
            ?? (is_array($b->contract) ? ($b->contract['type'] ?? $b->contract['mime'] ?? '') : '')
            ?? '';

        return [
            'id' => $b->id,
            'client_id' => $b->client_id,
            'date' => $b->date?->format('Y-m-d'),
            'month' => $b->month,
            'year' => $b->year,
            'platform' => $b->platform,
            'account' => $b->account,
            'brand' => $b->brand,
            'contact' => $b->contact,
            'product' => $b->product,
            'amount' => (string) $b->amount,
            'status' => $b->status,
            'due_date' => $b->due_date?->format('Y-m-d'),
            'usage_rights' => $b->usage_rights,
            'notes' => $b->notes,
            'contract' => $hasDownload ? [
                'name' => $contractName,
                'mime' => $contractMime,
                'size' => $b->contract_size,
                'downloadUrl' => url('/api/brand-deals/'.$b->client_id.'/contract'),
            ] : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $contract
     * @return array<string, mixed>|null
     */
    private function persistContractFromDataUrl(?array $contract): ?array
    {
        if (! is_array($contract) || empty($contract['data']) || ! is_string($contract['data'])) {
            return null;
        }

        $decoded = $this->decodeDataUrl($contract['data']);
        if (! $decoded) {
            return null;
        }
        [$binary, $mime] = $decoded;
        $disk = 'public';
        $ext = $this->extensionFromMime($mime, $contract['name'] ?? null);
        $path = 'brand-contracts/'.Str::uuid().'.'.$ext;
        Storage::disk($disk)->put($path, $binary);

        return [
            'contract_disk' => $disk,
            'contract_path' => $path,
            'contract_original_name' => is_string($contract['name'] ?? null) ? $contract['name'] : 'contract.'.$ext,
            'contract_mime' => $mime,
            'contract_size' => strlen($binary),
            'contract' => null,
            'contract_legacy_json' => false,
        ];
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function decodeDataUrl(string $dataUrl): ?array
    {
        if (! preg_match('#^data:([^;]+);base64,(.+)$#', $dataUrl, $m)) {
            return null;
        }
        $mime = trim($m[1]);
        $raw = base64_decode($m[2], true);
        if ($raw === false) {
            return null;
        }

        return [$raw, $mime ?: 'application/octet-stream'];
    }

    private function extensionFromMime(string $mime, ?string $originalName): string
    {
        $fromName = $originalName ? strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) : '';
        if ($fromName && preg_match('/^[a-z0-9]{2,5}$/', $fromName)) {
            return $fromName;
        }

        return match (true) {
            str_contains($mime, 'pdf') => 'pdf',
            str_contains($mime, 'png') => 'png',
            str_contains($mime, 'jpeg') => 'jpg',
            str_contains($mime, 'jpg') => 'jpg',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'word') => 'docx',
            default => 'bin',
        };
    }

    private function guessMimeFromName(string $name): string
    {
        return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }

    private function deleteContractIfPresent(?BrandDeal $row): void
    {
        if (! $row) {
            return;
        }
        if ($row->contract_disk && $row->contract_path) {
            try {
                Storage::disk($row->contract_disk)->delete($row->contract_path);
            } catch (\Throwable $ignored) {
            }
        }
    }
}
