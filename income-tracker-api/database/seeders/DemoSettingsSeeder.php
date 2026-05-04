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
