<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
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
    public function it_can_upload_a_file_with_valid_token()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file,
            'description' => 'Test file description'
        ], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'path',
                    'mime_type',
                    'size',
                    'description',
                    'created_at',
                    'updated_at'
                ]
            ]);

        Storage::disk('public')->assertExists('files/' . basename($response->json('data.path')));

        $this->assertDatabaseHas('files', [
            'name' => 'test.txt',
            'mime_type' => 'text/plain',
            'description' => 'Test file description'
        ]);
    }

    /** @test */
    public function it_validates_required_file_field()
    {
        $response = $this->postJson('/api/files', [], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_validates_file_size_limit()
    {
        $file = UploadedFile::fake()->create('test.txt', 11000, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_validates_file_type()
    {
        $file = UploadedFile::fake()->create('test.exe', 100, 'application/x-executable');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_validates_description_length()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');
        $longDescription = str_repeat('a', 501);

        $response = $this->postJson('/api/files', [
            'file' => $file,
            'description' => $longDescription
        ], [
            'Authorization' => self::VALID_TOKEN
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function it_accepts_valid_file_types()
    {
        $validTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];

        foreach ($validTypes as $extension => $mimeType) {
            $file = UploadedFile::fake()->create("test.{$extension}", 100, $mimeType);

            $response = $this->postJson('/api/files', [
                'file' => $file
            ], [
                'Authorization' => self::VALID_TOKEN
            ]);

            $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'File uploaded successfully.',
                ]);
        }
    }

    /** @test */
    public function it_rejects_file_upload_without_authorization_header()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_rejects_file_upload_with_invalid_token()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
            'Authorization' => self::INVALID_TOKEN
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_rejects_file_upload_with_wrong_bearer_format()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
            'Authorization' => self::TOKEN_WITHOUT_BEARER
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }

    /** @test */
    public function it_rejects_file_upload_with_empty_token()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
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
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
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
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files', [
            'file' => $file
        ], [
            'Authorization' => self::TOKEN_WITH_SPACES
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ]);
    }
}
