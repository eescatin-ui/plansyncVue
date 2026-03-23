<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create 
                            {--email=admin@plansync.com : Admin email}
                            {--password=admin123 : Admin password}
                            {--name=Administrator : Admin name}';
    
    protected $description = 'Create an admin user for the application';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if admin already exists
        if (User::where('email', $email)->exists()) {
            $this->error("Admin user with email {$email} already exists!");
            return 1;
        }

        // Create admin user
        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info('✅ Admin user created successfully!');
        $this->line('');
        $this->line('📋 Login Details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Password', $password],
                ['Admin Status', 'Yes'],
                ['Login URL', url('/admin/login')]
            ]
        );

        return 0;
    }
}