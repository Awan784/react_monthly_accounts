<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\Platform;
use App\Models\Setting;
use App\Models\UsageRight;
use App\Support\DemoUser;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private function defaultPricing(): array
    {
        return [
            'singleVideo' => 800,
            'bundle3' => 2100,
            'bundle5' => 3000,
            'starterRetainer' => 2000,
            'growthRetainer' => 3600,
            'scaleRetainer' => 5000,
            'usage30' => 750,
            'usage60' => 1200,
            'usage90' => 1800,
            'usage6mo' => 3000,
            'usage12mo' => 5000,
            'usageUnlimited' => 7500,
            'productPhotos' => 200,
            'rawFootage' => 500,
            'rushFee' => 100,
        ];
    }

    public function bootstrap()
    {
        $userId = DemoUser::id();

        $settings = Setting::query()->where('user_id', $userId)->first();

        return response()->json([
            'settings' => $settings ? [
                'taxRate' => (string) $settings->tax_rate,
                'incomeGoal' => (string) $settings->income_goal,
                'monthlyGoal' => (string) $settings->monthly_goal,
                'pricingVersion' => $settings->pricing_version ?? null,
                'pricing' => $settings->pricing ?? $this->defaultPricing(),
            ] : null,
            'platforms' => Platform::query()->where('user_id', $userId)->where('is_active', true)->orderBy('name')->get(),
            'accounts' => Account::query()->where('user_id', $userId)->where('is_active', true)->orderBy('name')->get(),
            'expenseCategories' => ExpenseCategory::query()->where('user_id', $userId)->where('is_active', true)->orderBy('name')->get(),
            'usageRights' => UsageRight::query()->where('user_id', $userId)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $userId = DemoUser::id();

        $data = $request->validate([
            'taxRate' => ['nullable', 'numeric'],
            'incomeGoal' => ['nullable', 'numeric'],
            'monthlyGoal' => ['nullable', 'numeric'],
            'pricingVersion' => ['nullable', 'string', 'max:50'],
            'pricing' => ['nullable', 'array'],
        ]);

        $setting = Setting::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'tax_rate' => $data['taxRate'] ?? 22,
                'income_goal' => $data['incomeGoal'] ?? 250000,
                'monthly_goal' => $data['monthlyGoal'] ?? 20000,
                'pricing_version' => $data['pricingVersion'] ?? 'mediaKitPricingV2',
                'pricing' => $data['pricing'] ?? $this->defaultPricing(),
            ]
        );

        return response()->json([
            'id' => $setting->id,
            'userId' => $setting->user_id,
            'taxRate' => (string) $setting->tax_rate,
            'incomeGoal' => (string) $setting->income_goal,
            'monthlyGoal' => (string) $setting->monthly_goal,
            'pricingVersion' => $setting->pricing_version,
            'pricing' => $setting->pricing,
        ]);
    }
}
