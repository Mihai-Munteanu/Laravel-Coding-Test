<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_retrieve_list_of_files()
    {
        $files = File::factory()->count(5)->create();

        $response = $this->getJson('/api/files');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'path',
                        'mime_type',
                        'size',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function it_returns_empty_list_when_no_files_exist()
    {
        $response = $this->getJson('/api/files');

        $response->assertStatus(200)
            ->assertJson([
                'data' => []
            ]);
    }

    /** @test */
    public function it_can_filter_files_by_mime_type()
    {
        File::factory()->create(['mime_type' => 'text/plain']);
        File::factory()->create(['mime_type' => 'image/jpeg']);
        File::factory()->create(['mime_type' => 'application/pdf']);

        $response = $this->getJson('/api/files?filter[mime_type]=text/plain');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('text/plain', $response->json('data.0.mime_type'));
    }

    /** @test */
    public function it_can_filter_files_by_name()
    {
        File::factory()->create(['name' => 'document.pdf']);
        File::factory()->create(['name' => 'image.jpg']);
        File::factory()->create(['name' => 'document.txt']);

        $response = $this->getJson('/api/files?filter[name]=document');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_files_by_description()
    {
        File::factory()->create(['description' => 'Important document']);
        File::factory()->create(['description' => 'Regular file']);
        File::factory()->create(['description' => 'Important image']);

        $response = $this->getJson('/api/files?filter[description]=Important');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_files_by_exact_creation_date()
    {
        $specificDate = '2024-01-15';

        // Create files on specific date
        File::factory()->create(['created_at' => $specificDate . ' 10:00:00']);
        File::factory()->create(['created_at' => $specificDate . ' 15:30:00']);

        // Create files on different dates
        File::factory()->create(['created_at' => '2024-01-14 10:00:00']);
        File::factory()->create(['created_at' => '2024-01-16 10:00:00']);

        $response = $this->getJson('/api/files?filter[created_on]=' . $specificDate);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_can_sort_files_by_name()
    {
        File::factory()->create(['name' => 'zebra.txt']);
        File::factory()->create(['name' => 'apple.txt']);
        File::factory()->create(['name' => 'banana.txt']);

        $response = $this->getJson('/api/files?sort=name');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('apple.txt', $data[0]['name']);
        $this->assertEquals('banana.txt', $data[1]['name']);
        $this->assertEquals('zebra.txt', $data[2]['name']);
    }

    /** @test */
    public function it_can_sort_files_by_size()
    {
        File::factory()->create(['size' => 1000]);
        File::factory()->create(['size' => 5000]);
        File::factory()->create(['size' => 2000]);

        $response = $this->getJson('/api/files?sort=size');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(1000, $data[0]['size']);
        $this->assertEquals(2000, $data[1]['size']);
        $this->assertEquals(5000, $data[2]['size']);
    }

    /** @test */
    public function it_can_sort_files_by_created_at_descending()
    {
        $file1 = File::factory()->create(['created_at' => now()->subDays(2)]);
        $file2 = File::factory()->create(['created_at' => now()->subDays(1)]);
        $file3 = File::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/files?sort=-created_at');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($file3->id, $data[0]['id']);
        $this->assertEquals($file2->id, $data[1]['id']);
        $this->assertEquals($file1->id, $data[2]['id']);
    }

    /** @test */
    public function it_can_paginate_files()
    {
        File::factory()->count(20)->create();

        $response = $this->getJson('/api/files?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function it_uses_default_pagination_when_per_page_not_specified()
    {
        File::factory()->count(20)->create();

        $response = $this->getJson('/api/files');

        $response->assertStatus(200);
        $this->assertCount(15, $response->json('data'));
    }

    /** @test */
    public function it_allows_file_listing_without_token()
    {
        File::factory()->count(3)->create();

        $response = $this->getJson('/api/files');

        $response->assertStatus(200);
    }
}
