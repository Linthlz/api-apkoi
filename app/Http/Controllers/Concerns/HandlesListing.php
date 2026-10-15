<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HandlesListing
{
    /**
     * Jumlah item per halaman dari query ?per_page= (default 5, maksimal 100).
     */
    protected function perPage(Request $request, int $default = 5): int
    {
        return min(max((int) $request->query('per_page', $default), 1), 100);
    }

    /**
     * Bentuk respons daftar yang dipakai semua endpoint index.
     */
    protected function paginated(string $message, LengthAwarePaginator $data, ?callable $map = null)
    {
        $items = collect($data->items());

        return response()->json([
            'message' => $message,
            'data' => $map ? $items->map($map)->values() : $items->values(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ], 200);
    }

    /**
     * Slug unik untuk model tertentu, mis. "judul", "judul-1", "judul-2".
     */
    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $count = 1;

        while (
            $modelClass::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
