<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileTypes = [
            // Documents
            ['mime_type' => 'application/pdf', 'extension' => 'pdf'],
            ['mime_type' => 'application/msword', 'extension' => 'doc'],
            ['mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'extension' => 'docx'],
            ['mime_type' => 'application/vnd.ms-excel', 'extension' => 'xls'],
            ['mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'extension' => 'xlsx'],

            // Images
            ['mime_type' => 'image/jpeg', 'extension' => 'jpg'],
            ['mime_type' => 'image/png', 'extension' => 'png'],
            ['mime_type' => 'image/gif', 'extension' => 'gif'],
            ['mime_type' => 'image/webp', 'extension' => 'webp'],

            // Text Files
            ['mime_type' => 'text/plain', 'extension' => 'txt'],
        ];

        for ($i = 0; $i < 100; $i++) {
            $fileType = fake()->randomElement($fileTypes);

            // Generate realistic file sizes based on file type
            $size = $this->generateFileSize($fileType['mime_type']);

            // Generate realistic creation dates (last 6 months)
            $createdAt = fake()->dateTimeBetween('-6 months', 'now');

            File::create([
                'name' => fake()->words(2, true) . ' ' . fake()->randomElement(['Document', 'File', 'Report', 'Data', 'Archive', 'Backup', 'Template', 'Draft', 'Final', 'Version']),
                'path' => 'files/' . Str::slug(fake()->words(2, true)) . '_' . ($i + 1) . '.' . $fileType['extension'],
                'mime_type' => $fileType['mime_type'],
                'size' => $size,
                'description' => fake()->optional(0.8)->sentence(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    /**
     * Generate realistic file sizes based on MIME type
     */
    private function generateFileSize(string $mimeType): int
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => fake()->numberBetween(50 * 1024, 5 * 1024 * 1024), // 50KB - 5MB
            str_starts_with($mimeType, 'application/pdf') => fake()->numberBetween(100 * 1024, 10 * 1024 * 1024), // 100KB - 10MB
            str_starts_with($mimeType, 'application/msword') => fake()->numberBetween(50 * 1024, 5 * 1024 * 1024), // 50KB - 5MB
            str_starts_with($mimeType, 'application/vnd.openxmlformats') => fake()->numberBetween(50 * 1024, 5 * 1024 * 1024), // 50KB - 5MB
            str_starts_with($mimeType, 'text/') => fake()->numberBetween(1 * 1024, 100 * 1024), // 1KB - 100KB
            default => fake()->numberBetween(10 * 1024, 1024 * 1024), // 10KB - 1MB
        };
    }
}
