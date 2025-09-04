<?php

namespace Database\Factories\API;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

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
            'title' => 'names',
            'background_image' => 1,//fake()->intege(),
            'content_text' => 'parent',
            'description' => fake()->paragraph(),
            // 'media_link' => fake()->imageUrl(),
            'content_category' => $category[rand(0,2)],
            'lang' => $languges[rand(0,1)],
            "is_original" => true,
            "auto_translate" => true,
            'created_at' => now(),
            'updated_at' => now(),
            'is_draft' => true,
            'updated_by'=> User::inRandomOrder()->first(),
            'tenant_id' => 1
        ];
    }
}
