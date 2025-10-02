<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création des rôles par défaut de l'application
    
        // User::factory(10)->create();

     //  User::factory()->create([
      // 'name' => 'Test User',
      // 'name' => 'Test User',
       //'prenom' => 'Test User',
       // 'email' => 'test@example.com',
       // ]);
        $this->call([
        RoleSeeder::class,
        UserSeeder::class,
    ]);
    }
}
