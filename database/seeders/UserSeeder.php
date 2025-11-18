<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@find.sn',
            'phone' => '+221771234567',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_verified' => true,
            'phone_verified' => true,
            'bio' => 'Administrateur de la plateforme Find',
            'city' => 'Dakar',
            'country' => 'Senegal',
            'reputation_score' => 1000,
        ]);

        // Créer un modérateur
        User::create([
            'name' => 'Modérateur',
            'email' => 'moderator@find.sn',
            'phone' => '+221771234568',
            'password' => bcrypt('password'),
            'role' => 'moderator',
            'is_verified' => true,
            'phone_verified' => true,
            'bio' => 'Modérateur de la plateforme Find',
            'city' => 'Dakar',
            'country' => 'Senegal',
            'reputation_score' => 500,
        ]);

        // Créer des utilisateurs normaux
        User::create([
            'name' => 'Moussa Diop',
            'email' => 'moussa@example.com',
            'phone' => '+221771234569',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_verified' => true,
            'bio' => 'Utilisateur actif sur Find',
            'city' => 'Dakar',
            'country' => 'Senegal',
        ]);

        User::create([
            'name' => 'Fatou Sall',
            'email' => 'fatou@example.com',
            'phone' => '+221771234570',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_verified' => true,
            'bio' => 'Passionnée de technologie',
            'city' => 'Thiès',
            'country' => 'Senegal',
        ]);

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@find.sn / password');
        $this->command->info('Moderator: moderator@find.sn / password');
    }
}
