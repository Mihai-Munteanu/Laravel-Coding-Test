<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Store an uploaded file and create a database record
     */
    public function storeFile(UploadedFile $uploadedFile, ?string $description = null): File
    {
        // Generate unique filename
        $filename = $this->generateUniqueFilename($uploadedFile);

        // Store file in storage
        $path = $uploadedFile->storeAs('files', $filename, 'public');

        // Create database record
        return File::create([
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'description' => $description,
        ]);
    }

    /**
     * Delete a file from storage and database
     */
    public function deleteFile(File $file): bool
    {
        if (!Storage::disk('public')->exists($file->path)) {
            throw new \Exception('File not found in storage.');
        }

        if (!Storage::disk('public')->delete($file->path)) {
            throw new \Exception('Failed to delete file from storage.');
        }

        if (!$file->delete()) {
            throw new \Exception('Failed to delete file from database.');
        }

        return true;
    }

    /**
     * Get file content for download
     */
    public function getFileContent(File $file): string
    {
        return Storage::disk('public')->get($file->path);
    }

    /**
     * Generate a unique filename to prevent conflicts
     */
    private function generateUniqueFilename(UploadedFile $uploadedFile): string
    {
        $extension = $uploadedFile->getClientOriginalExtension();
        $basename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($basename);

        // Add timestamp and random string to ensure uniqueness
        $uniqueId = time() . '_' . Str::random(8);

        return "{$slug}_{$uniqueId}.{$extension}";
    }

    /**
     * Get file download response
     */
    public function getDownloadResponse(File $file)
    {
        $content = $this->getFileContent($file);

        return response($content)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->name . '"')
            ->header('Content-Length', $file->size);
    }
}
