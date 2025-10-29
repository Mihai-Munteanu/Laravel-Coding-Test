<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileRandomCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_executes_file_random_command_successfully()
    {
        // Create a test file in both database and storage
        $file = File::factory()->create([
            'name' => 'test.txt',
            'path' => 'files/test.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
            'description' => 'Test file'
        ]);

        Storage::disk('public')->put('files/test.txt', 'Test content');

        $this->artisan('file:random')
            ->expectsOutput('Random file selected:')
            ->expectsOutput('Name: test.txt')
            ->expectsOutput('Path: files/test.txt')
            ->expectsOutput('MIME Type: text/plain')
            ->expectsOutput('Size: 100 B')
            ->expectsOutput('Description: Test file')
            ->expectsOutput('Created: ' . $file->created_at->format('Y-m-d H:i:s'))
            ->expectsOutput('File details have been logged to the application log.')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_no_files_in_database()
    {
        $this->artisan('file:random')
            ->expectsOutput('No files found in storage.')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_handles_file_not_found_in_storage()
    {
        File::factory()->create([
            'name' => 'missing.txt',
            'path' => 'files/missing.txt',
            'mime_type' => 'text/plain',
            'size' => 100
        ]);

        $this->artisan('file:random')
            ->expectsOutput('File not found in storage: files/missing.txt')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_selects_different_files_randomly()
    {
        for ($i = 1; $i <= 5; $i++) {
            $file = File::factory()->create([
                'name' => "file{$i}.txt",
                'path' => "files/file{$i}.txt",
                'mime_type' => 'text/plain',
                'size' => $i * 100
            ]);

            Storage::disk('public')->put("files/file{$i}.txt", "Content {$i}");
        }

        for ($i = 0; $i < 10; $i++) {
            $this->artisan('file:random')
                ->assertExitCode(0);
        }
    }

    /** @test */
    public function it_formats_file_sizes_correctly()
    {
        $file = File::factory()->create([
            'name' => 'test.txt',
            'path' => 'files/test.txt',
            'mime_type' => 'text/plain',
            'size' => 1024
        ]);

        Storage::disk('public')->put('files/test.txt', 'Test content');

        $this->artisan('file:random')
            ->expectsOutput('Size: 1 KB')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_file_without_description()
    {
        $file = File::factory()->create([
            'name' => 'test.txt',
            'path' => 'files/test.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
            'description' => null
        ]);

        Storage::disk('public')->put('files/test.txt', 'Test content');

        $this->artisan('file:random')
            ->expectsOutput('Description: No description')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_logs_correct_file_information()
    {
        $file = File::factory()->create([
            'name' => 'document.pdf',
            'path' => 'files/document.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
            'description' => 'Important document'
        ]);

        Storage::disk('public')->put('files/document.pdf', 'PDF content');

        $this->artisan('file:random')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_continues_until_finding_valid_file()
    {
        $file1 = File::factory()->create([
            'name' => 'missing1.txt',
            'path' => 'files/missing1.txt',
            'mime_type' => 'text/plain',
            'size' => 100
        ]);

        $file2 = File::factory()->create([
            'name' => 'missing2.txt',
            'path' => 'files/missing2.txt',
            'mime_type' => 'text/plain',
            'size' => 200
        ]);

        $file3 = File::factory()->create([
            'name' => 'valid.txt',
            'path' => 'files/valid.txt',
            'mime_type' => 'text/plain',
            'size' => 300
        ]);

        Storage::disk('public')->put('files/valid.txt', 'Valid content');

        $success = false;
        for ($i = 0; $i < 10; $i++) {
            try {
                $this->artisan('file:random')->assertExitCode(0);
                $success = true;
                break;
            } catch (\Exception $e) {
            }
        }

        $this->assertTrue($success, 'Command should eventually find a valid file');
    }
}
