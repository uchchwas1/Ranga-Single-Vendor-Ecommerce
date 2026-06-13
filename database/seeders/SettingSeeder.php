<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the default white-label settings.
 */
class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['group' => 'general', 'key' => 'brand_name', 'value' => config('ranga.brand.name'), 'is_public' => true],
            ['group' => 'general', 'key' => 'brand_tagline', 'value' => config('ranga.brand.tagline'), 'is_public' => true],
            ['group' => 'general', 'key' => 'brand_color', 'value' => config('ranga.brand.color'), 'is_public' => true],
            ['group' => 'general', 'key' => 'currency', 'value' => config('ranga.defaults.currency'), 'is_public' => true],
            ['group' => 'general', 'key' => 'currency_symbol', 'value' => config('ranga.defaults.currency_symbol'), 'is_public' => true],
            ['group' => 'general', 'key' => 'timezone', 'value' => config('ranga.defaults.timezone'), 'is_public' => true],
            ['group' => 'general', 'key' => 'locale', 'value' => config('ranga.defaults.locale'), 'is_public' => true],
            ['group' => 'security', 'key' => 'admin_2fa_required', 'value' => true, 'is_public' => false],
        ];

        foreach ($defaults as $setting) {
            Setting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'is_public' => $setting['is_public']],
            );
        }
    }
}
