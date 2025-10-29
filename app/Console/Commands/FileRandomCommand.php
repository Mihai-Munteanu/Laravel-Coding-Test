<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileRandomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Select a random file from storage and log its details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get a random file from the database
        $file = File::inRandomOrder()->first();

        if (!$file) {
            $this->error('No files found in storage.');
            return 1;
        }

        if (!Storage::disk('public')->exists($file->path)) {
            $this->error('File not found in storage: ' . $file->path);
            return 1;
        }

        // Display file details in console
        $this->info('Random file selected:');
        $this->line('Name: ' . $file->name);
        $this->line('Path: ' . $file->path);
        $this->line('MIME Type: ' . $file->mime_type);
        $this->line('Size: ' . $this->formatBytes($file->size));
        $this->line('Description: ' . ($file->description ?? 'No description'));
        $this->line('Created: ' . $file->created_at->format('Y-m-d H:i:s'));

        // Log the file details
        Log::info('Random file selected by file:random command', [
            'file_id' => $file->id,
            'name' => $file->name,
            'path' => $file->path,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'description' => $file->description,
            'created_at' => $file->created_at,
        ]);

        $this->info('File details have been logged to the application log.');

        return 0;
    }

    /**
     * Format bytes into human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
