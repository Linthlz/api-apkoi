<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Journal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JournalController extends Controller
{
    use AuthorizesRequests, HandlesListing;

    private const FIELDS = [
        'title', 'journal_author', 'journal_name', 'journal_publisher', 'publish_date',
        'doi', 'url', 'member_name', 'journal_abstract',
    ];

    private function rules(?Journal $journal = null): array
    {
        $required = $journal ? 'sometimes|required' : 'required';

        return [
            'title' => "$required|string|max:255|unique:journals,title" . ($journal ? ',' . $journal->id : ''),
            'journal_author' => "$required|string",
            'publish_date' => "$required|date",
            // minimal salah satu dari DOI atau link artikel harus ada
            'doi' => ($journal ? 'nullable' : 'nullable|required_without:url') . '|string|max:255',
            'url' => ($journal ? 'nullable' : 'nullable|required_without:doi') . '|url|max:1000',
            'journal_name' => 'nullable|string|max:255',
            'journal_publisher' => 'nullable|string|max:255',
            'member_name' => 'nullable|string|max:255',
            'journal_abstract' => 'nullable|string',
        ];
    }

    public function store(Request $request)
    {
        $this->authorize('manage jurnal');
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $journal = Journal::create([
                ...$request->only(self::FIELDS),
                'public_id' => Str::uuid()->toString(),
                'slug' => $this->uniqueSlug(Journal::class, $request->title),
                'publisher_id' => Auth::user()->id,
                'published_at' => now(),
            ]);

            return response()->json([
                'message' => 'Jurnal berhasil dibuat!',
                'data' => $journal,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $data = Journal::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('publish_date')
            ->latest()
            ->paginate($this->perPage($request));

        return $this->paginated('Journal Data', $data);
    }

    public function show($slug)
    {
        $data = Journal::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return response()->json([
            'message' => 'Journal berhasil dicari!',
            'data' => $data,
        ], 200);
    }

    public function update(Request $request, $slug)
    {
        $data = Journal::where('slug', $slug)->firstOrFail();
        $this->authorize('manage jurnal');

        $validator = Validator::make($request->all(), $this->rules($data));
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $data->fill($request->only(self::FIELDS));
            if ($data->isDirty('title')) {
                $data->slug = $this->uniqueSlug(Journal::class, $data->title, $data->id);
            }
            $data->save();

            DB::commit();

            return response()->json([
                'message' => 'Journal Updated',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($slug)
    {
        $this->authorize('manage jurnal');
        $data = Journal::where('slug', $slug)->firstOrFail();

        try {
            DB::beginTransaction();
            $data->delete();
            DB::commit();

            return response()->json([
                'message' => 'Journal berhasil dihapus!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Oops! Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
