<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📧 You can now login with:');
        $this->command->info('   Email: admin@find.sn');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('🔧 Other accounts:');
        $this->command->info('   Moderator: moderator@find.sn / password');
        $this->command->info('   User: moussa@example.com / password');
    }
}
