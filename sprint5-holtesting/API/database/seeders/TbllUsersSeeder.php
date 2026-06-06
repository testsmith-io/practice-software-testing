<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TbllUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [];

        for ($i = 1; $i <= 20; $i++) {
            $users[] = [
                'id' => Str::ulid()->toBase32(),
                'first_name' => "Tbll{$i}",
                'last_name' => 'User',
                'street' => 'Test street 1',
                'city' => 'Testville',
                'state' => null,
                'country' => 'The Netherlands',
                'postal_code' => null,
                'phone' => null,
                'dob' => '1990-01-01',
                'email' => "tbll{$i}@gmail.com",
                'password' => app('hash')->make('tbll100!'),
                'role' => 'user'
            ];
        }

        DB::table('users')->insert($users);
    }
}
