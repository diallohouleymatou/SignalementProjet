<?php

namespace Database\Factories;

use App\Modules\Comment\Models\Comment;
use App\Modules\Signalement\Models\Signalement;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'signalement_id' => Signalement::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => $this->faker->paragraph(2),
            'is_approved' => true,
            'likes' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function reply(Comment $parentComment): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentComment->id,
            'signalement_id' => $parentComment->signalement_id,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }
}
