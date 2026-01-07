<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les utilisateurs de test
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Créer l'utilisateur admin pour le CRM (sera utilisé par DataSeeder)
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@dargatech.com',
        ]);

        // Appeler le DataSeeder pour créer les données CRM
        $this->call([
            DataSeeder::class,
        ]);
    }
}
