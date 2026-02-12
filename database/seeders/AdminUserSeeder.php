<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@visionaries.pro'],
            [
                'display_name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified' => true,
                'onboarding_completed' => true,
                'auth_provider' => 'email',
            ]
        );
    }
}
