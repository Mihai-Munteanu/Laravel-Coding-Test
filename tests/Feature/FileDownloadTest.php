<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_download_file_with_valid_id()
    {
        $file = File::factory()->create([
            'name' => 'document.pdf',
            'path' => 'files/document.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'description' => 'Important document'
        ]);

        Storage::disk('public')->put('files/document.pdf', 'PDF content');

        $response = $this->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="document.pdf"');

        $this->assertEquals('PDF content', $response->getContent());
    }

    /** @test */
    public function it_returns_404_for_nonexistent_file_download()
    {
        $response = $this->getJson('/api/files/999/download');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_when_file_not_in_storage()
    {
        $file = File::factory()->create([
            'name' => 'missing.pdf',
            'path' => 'files/missing.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024
        ]);

        $response = $this->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'File not found in storage.'
            ]);
    }

    /** @test */
    public function it_allows_file_download_without_token()
    {
        $file = File::factory()->create([
            'name' => 'public.pdf',
            'path' => 'files/public.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024
        ]);

        Storage::disk('public')->put('files/public.pdf', 'Public content');

        $response = $this->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="public.pdf"');

        $this->assertEquals('Public content', $response->getContent());
    }
}
