<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //Using users in the database using eloquent to get data from 
        $randomUsers= User::pluck('id')->toArray();
        $randomUser= $this->faker->randomElement($randomUsers);

        return [
            'blog_title'=>fake()->sentence(6),
            'blog_content'=>fake()->sentence(20),
            'user_id'=>$randomUser,
        ];
    }
}
