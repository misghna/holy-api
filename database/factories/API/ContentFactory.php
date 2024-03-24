<?php

namespace Database\Factories\API;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\API\Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['Text','Image','Video','Donate'];
        $languges = ['English','English'];
        $category = ['Home','News','Media'];
        return [
            'type' => $types[rand(0,2)],
            'background_image' => fake()->imageUrl(),
            'content' => fake()->paragraph(),
            'media_link' => fake()->imageUrl(),
            'content_category' => $category[rand(0,2)],
            'language' => $languges[rand(0,1)],
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
