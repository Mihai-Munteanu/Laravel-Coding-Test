<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileDestroyTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_TOKEN = 'Bearer artificially-token';
    private const INVALID_TOKEN = 'Bearer invalid-token';
    private const TOKEN_WITHOUT_BEARER = 'artificially-token';
    private const TOKEN_WITH_SPACES = 'Bearer  artificially-token  ';
    private const TOKEN_WRONG_CASE = 'Bearer Artificially-Token';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_delete_file_with_valid_token()
    {
        $file = File::factory()->create([
            'name' => 'delete-me.txt',
            'path' => 'files/delete-me.txt',
            'mime_type' => 'text/plain',
            'size' => 100
        ]);

        Storage::disk('public')->put('files/delete-me.txt', 'Content to delete');

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully.'
            ]);

        $this->assertDatabaseMissing('files', [
            'id' => $file->id
        ]);

        Storage::disk('public')->assertMissing('files/delete-me.txt');
    }

    /** @test */
    public function it_rejects_deletion_without_token()
    {
        $file = File::factory()->create([
            'name' => 'protected.txt',
            'path' => 'files/protected.txt',
            'mime_type' => 'text/plain',
            'size' => 100
        ]);

        Storage::disk('public')->put('files/protected.txt', 'Content');

        $response = $this->deleteJson("/api/files/{$file->id}");

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);

        $this->assertDatabaseHas('files', [
            'id' => $file->id
        ]);
        Storage::disk('public')->assertExists('files/protected.txt');
    }

    /** @test */
    public function it_rejects_deletion_with_invalid_token()
    {
        $file = File::factory()->create([
            'name' => 'protected.txt',
            'path' => 'files/protected.txt',
            'mime_type' => 'text/plain',
            'size' => 100
        ]);

        Storage::disk('public')->put('files/protected.txt', 'Content');

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::INVALID_TOKEN
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);

        $this->assertDatabaseHas('files', [
            'id' => $file->id
        ]);
        Storage::disk('public')->assertExists('files/protected.txt');
    }

    /** @test */
    public function it_deletes_file_with_large_size()
    {
        $file = File::factory()->create([
            'name' => 'large-file.txt',
            'path' => 'files/large-file.txt',
            'mime_type' => 'text/plain',
            'size' => 10485760
        ]);

        Storage::disk('public')->put('files/large-file.txt', str_repeat('A', 1000));

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully.'
            ]);

        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing('files/large-file.txt');
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_file_from_database()
    {
        $response = $this->deleteJson('/api/files/999', [], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_error_when_deletion_fails_due_to_non_existent_file()
    {
        $file = File::factory()->create();

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'File not found in storage.'
            ]);

        $this->assertDatabaseHas('files', [
            'id' => $file->id
        ]);
    }

    /** @test */
    public function it_rejects_deletion_without_authorization_header()
    {
        $file = File::factory()->create();
        Storage::disk('public')->put($file->path, 'Test content');

        $response = $this->deleteJson("/api/files/{$file->id}");

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_rejects_deletion_with_wrong_bearer_format()
    {
        $file = File::factory()->create();
        Storage::disk('public')->put($file->path, 'Test content');

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::TOKEN_WITHOUT_BEARER
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_rejects_deletion_with_empty_token()
    {
        $file = File::factory()->create();
        Storage::disk('public')->put($file->path, 'Test content');

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => 'Bearer '
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_handles_case_sensitive_token()
    {
        $file = File::factory()->create();
        Storage::disk('public')->put($file->path, 'Test content');

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::TOKEN_WRONG_CASE
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_handles_extra_spaces_in_token()
    {
        $file = File::factory()->create();
        Storage::disk('public')->put($file->path, 'Test content');

        $response = $this->deleteJson("/api/files/{$file->id}", [], [
            'Authorization' => self::TOKEN_WITH_SPACES
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }
}
