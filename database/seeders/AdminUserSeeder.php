<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Helpers\EncryptHelper;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => EncryptHelper::encrypt('admin@gmail.com')],
            [
                'name' => EncryptHelper::encrypt('Admin'),
                'password' => Hash::make('admin123'), // Ganti dengan password aman
            ]
        );
    }
}
