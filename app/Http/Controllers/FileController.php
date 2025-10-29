<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
                AllowedFilter::scope('size_range', 'sizeBetween'),
                AllowedFilter::scope('created_after', 'createdAfter'),
                AllowedFilter::scope('created_before', 'createdBefore'),
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
}
