<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Root database seeder.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            CatalogueSeeder::class,
        ]);

        $admin = User::query()->firstOrCreate(
            ['email' => (string) env('RANGA_ADMIN_EMAIL', 'admin@ranga.test')],
            [
                'name' => 'Platform Admin',
                'password' => (string) env('RANGA_ADMIN_PASSWORD', 'ChangeMe!123'),
                'locale' => (string) config('ranga.defaults.locale'),
                'timezone' => (string) config('ranga.defaults.timezone'),
                'referral_code' => Str::upper(Str::random(10)),
            ],
        );

        $admin->forceFill(['email_verified_at' => Date::now()])->save();
        $admin->syncRoles(['super-admin']);
    }
}
