<?php

namespace Database\Factories;

use App\Modules\Signalement\Models\Signalement;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SignalementFactory extends Factory
{
    protected $model = Signalement::class;

    public function definition(): array
    {
        $types = ['objet', 'personne'];
        $statuses = ['en_cours', 'retrouve', 'faux'];

        // Coordonnées aléatoires autour de Dakar, Sénégal
        $latitude = 14.6937 + (rand(-100, 100) / 1000); // ~14.5 to 14.8
        $longitude = -17.4441 + (rand(-100, 100) / 1000); // ~-17.3 to -17.5

        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'date_loss' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'location' => $this->faker->randomElement([
                'Place de l\'Indépendance, Dakar',
                'Marché Sandaga, Dakar',
                'Université Cheikh Anta Diop',
                'Gare routière Pompiers',
                'Corniche de Dakar',
                'Plateau, Dakar',
                'Almadies',
                'Yoff',
                'Ouakam',
            ]),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'city' => 'Dakar',
            'country' => 'Senegal',
            'status' => $this->faker->randomElement($statuses),
            'views' => $this->faker->numberBetween(0, 1000),
            'shares' => $this->faker->numberBetween(0, 50),
            'reward_amount' => $this->faker->optional(0.3)->randomFloat(2, 5000, 100000),
            'reward_currency' => 'XOF',
            'contact_phone' => $this->faker->optional(0.5)->phoneNumber(),
            'contact_email' => $this->faker->optional(0.3)->email(),
            'is_approved' => true,
            'approved_at' => now(),
        ];
    }

    public function objet(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'objet',
        ]);
    }

    public function personne(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personne',
        ]);
    }

    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'en_cours',
        ]);
    }

    public function retrouve(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'retrouve',
        ]);
    }

    public function withReward(): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_amount' => $this->faker->randomFloat(2, 10000, 200000),
            'reward_currency' => 'XOF',
        ]);
    }
}
