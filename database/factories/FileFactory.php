<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = $this->faker->word() . '.' . $this->faker->fileExtension();
        $path = 'files/' . Str::slug($this->faker->word()) . '_' . time() . '_' . Str::random(8) . '.' . $this->faker->fileExtension();

        return [
            'name' => $filename,
            'path' => $path,
            'mime_type' => $this->faker->mimeType(),
            'size' => $this->faker->numberBetween(100, 1000000),
            'description' => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
