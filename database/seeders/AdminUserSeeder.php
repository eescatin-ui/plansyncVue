<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@plansync.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);
        
        // You can also update existing users to be admins
        // User::where('email', 'existing@email.com')->update(['is_admin' => true]);
    }
}