<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Http\Requests\StoreFileRequest;
use App\Services\FileService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class FileController extends Controller
{
    public function index(): LengthAwarePaginator
    {
        return QueryBuilder::for(File::class)
            ->allowedFilters([
                AllowedFilter::exact('mime_type'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('description'),
                AllowedFilter::scope('created_on', 'createdOn'),
            ])
            ->allowedSorts([
                AllowedSort::field('name'),
                AllowedSort::field('size'),
                AllowedSort::field('created_at'),
                AllowedSort::field('updated_at'),
            ])
            ->defaultSort('-created_at')
            ->paginate(request('per_page', 15));
    }

    public function store(StoreFileRequest $request, FileService $fileService): JsonResponse
    {
        $file = $fileService->storeFile(
            $request->file('file'),
            $request->input('description')
        );

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data' => $file
        ], 201);
    }

    public function show(File $file): JsonResponse
    {
        // Check if file actually exists in storage
        if (!Storage::disk('public')->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found in storage.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $file
        ]);
    }

    public function download(File $file, FileService $fileService)
    {
        if (!Storage::disk('public')->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found in storage.'
            ], 404);
        }

        return $fileService->getDownloadResponse($file);
    }

    public function destroy(File $file, FileService $fileService): JsonResponse
    {
        try {
            $fileService->deleteFile($file);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.'
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'File not found in storage.') {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in storage.'
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file. Please try again.'
            ], 500);
        }
    }
}
