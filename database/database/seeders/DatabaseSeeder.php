<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            "name" => "Admin",
            "email" => "admin@daycare.test",
            "password" => Hash::make("password"),
            "role" => "admin",
        ]);

        // Staff user + profile
        $staffUser = User::create([
            "name" => "Jane Caregiver",
            "email" => "staff@daycare.test",
            "password" => Hash::make("password"),
            "role" => "staff",
        ]);

        Staff::create([
            "user_id" => $staffUser->id,
            "name" => "Jane Caregiver",
            "position" => "Lead Caregiver",
            "phone" => "0170000000",
            "email" => "staff@daycare.test",
            "hire_date" => now()->subYear(),
            "status" => "active",
        ]);

        // Parent/employee user + profile
        $parentUser = User::create([
            "name" => "John Employee",
            "email" => "parent@daycare.test",
            "password" => Hash::make("password"),
            "role" => "parent",
        ]);

        Employee::create([
            "user_id" => $parentUser->id,
            "name" => "John Employee",
            "employee_code" => "EMP-001",
            "department" => "Engineering",
            "designation" => "Software Engineer",
            "phone" => "0180000000",
            "email" => "parent@daycare.test",
            "address" => "Dhaka, Bangladesh",
        ]);
    }
}
