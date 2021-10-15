<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'              =>  'Admin',
            'email'             =>  'admin_financapp@gmail.com',
            'password'          =>  Hash::make('tcc12345'),
            'remember_token'    =>  rand(10),
        ]);
    }
}
