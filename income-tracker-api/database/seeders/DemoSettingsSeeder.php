<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\Platform;
use App\Models\Setting;
use App\Models\UsageRight;
use App\Support\DemoUser;
use Illuminate\Database\Seeder;

class DemoSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DemoUser::id();

        Setting::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'tax_rate' => 22,
                'income_goal' => 250000,
                'monthly_goal' => 20000,
                'pricing_version' => 'mediaKitPricingV2',
                'pricing' => [
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
                ],
            ]
        );

        foreach (['DynamiteFinds', 'DynamiteFindsMore', 'Trisha'] as $name) {
            Account::query()->firstOrCreate(['user_id' => $userId, 'name' => $name], ['is_active' => true]);
        }

        foreach (['TikTok Shop', 'Amazon', 'Facebook', 'YouTube', 'Instagram', 'Snapchat', 'Other'] as $name) {
            Platform::query()->firstOrCreate(['user_id' => $userId, 'name' => $name], ['is_active' => true]);
        }

        foreach (['Product Purchases', 'Shipping', 'Subscriptions', 'Software', 'Mileage', 'Equipment', 'Props', 'Ads', 'Misc'] as $name) {
            ExpenseCategory::query()->firstOrCreate(['user_id' => $userId, 'name' => $name], ['is_active' => true]);
        }

        foreach (['Organic Only', '30-Day Paid Usage', '60-Day Paid Usage', '90-Day Paid Usage', '6-Month Paid Usage', '12-Month Paid Usage', 'Unlimited Usage', 'Not Included'] as $name) {
            UsageRight::query()->firstOrCreate(['user_id' => $userId, 'name' => $name], ['is_active' => true]);
        }
    }
}
