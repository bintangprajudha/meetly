<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'ketutangga',
            'email' => 'ketut@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        User::create([
            'name' => 'bintangprajudha',
            'email' => 'bintang@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        User::create([
            'name' => 'alfarazel',
            'email' => 'razel@gmail.com',
            'password' => bcrypt('12345678'),
        ]); 

        User::create([
            'name' => 'alfinsyah',
            'email' => 'alpin@gmail.com',
            'password' => bcrypt('12345678'),
        ]); 

        User::create([
            'name' => 'revanzayyan',
            'email' => 'revan@gmail.com',
            'password' => bcrypt('12345678'),
        ]); 
        
        User::create([
            'name' => 'raskavrinho',
            'email' => 'raska@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
    }
}
