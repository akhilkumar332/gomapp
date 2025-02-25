<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckAdminUser extends Command
{
    protected $signature = 'user:check-admin';
    protected $description = 'Check if admin user exists';

    public function handle()
    {
        $admin = User::where('role', 'admin')->first();
        
        if ($admin) {
            $this->info('Admin user exists:');
            $this->table(
                ['ID', 'Name', 'Email', 'Role'],
                [[$admin->id, $admin->name, $admin->email, $admin->role]]
            );
        } else {
            $this->error('No admin user found');
            
            if ($this->confirm('Would you like to create an admin user?')) {
                $admin = User::create([
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('admin123'),
                    'role' => 'admin',
                    'status' => 'active'
                ]);
                
                $this->info('Admin user created successfully:');
                $this->table(
                    ['ID', 'Name', 'Email', 'Role'],
                    [[$admin->id, $admin->name, $admin->email, $admin->role]]
                );
            }
        }
    }
}
