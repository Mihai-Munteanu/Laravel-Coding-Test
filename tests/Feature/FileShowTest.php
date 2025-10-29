<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_show_file_with_valid_id()
    {
        $file = File::factory()->create([
            'name' => 'test.txt',
            'path' => 'files/test.txt',
            'mime_type' => 'text/plain',
            'size' => 1024,
            'description' => 'Test file'
        ]);

        Storage::disk('public')->put('files/test.txt', 'Test content');

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $file->id,
                    'name' => 'test.txt',
                    'path' => 'files/test.txt',
                    'mime_type' => 'text/plain',
                    'size' => 1024,
                    'description' => 'Test file'
                ]
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'name', 'path', 'mime_type', 'size', 'description', 'created_at', 'updated_at'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_file()
    {
        $response = $this->getJson('/api/files/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_when_file_not_in_storage()
    {
        $file = File::factory()->create([
            'name' => 'missing.txt',
            'path' => 'files/missing.txt',
            'mime_type' => 'text/plain',
            'size' => 100
        ]);

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'File not found in storage.'
            ]);
    }

    /** @test */
    public function it_allows_file_retrieval_without_token()
    {
        $file = File::factory()->create();

        Storage::disk('public')->put($file->path, 'Test content');

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(200);
    }
}
