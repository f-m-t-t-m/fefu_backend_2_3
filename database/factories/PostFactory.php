<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->realTextBetween(5, 30),
            'text' => $this->faker->realTextBetween(250, 300),
            'user_id' => $this->faker->randomElement(User::query()->pluck('id')),
            'created_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
        ];
    }
}
