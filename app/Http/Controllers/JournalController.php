<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JournalController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request){
        $this->authorize('manage jurnal');
        $validator = Validator::make($request->all(), [
            'title'=>'required|string|unique:journals,title',
            'journal_author'=>'required|string',
            'publish_date'=>'required',
            'doi'=>'required|string',
            'journal_abstract'=>'required|string',
            'journal_file'=>'required|mimes:pdf|max:5000',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        try{
            // Public ID And Slug
            $uuid = Str::uuid()->toString();
            $slug = Str::slug($request->title);

            // Upload image
            $filePath = $request->file('journal_file')->store('journal/files', 'public');

            $journal = Journal::create([
                'public_id'=>$uuid,
                'title'=>$request->title,
                'journal_author'=>$request->journal_author,
                'publish_date'=>$request->publish_date,
                'doi'=>$request->doi,
                'journal_abstract'=>$request->journal_abstract,
                'journal_file'=>basename($filePath),
                'slug'=>$slug,
                'publisher_id'=>Auth::user()->id,
                'published_at'=>now(),
            ]);

            return response()->json([
                'message'=>'Jurnal berhasil dibuat!',
                'data'=>$journal,
            ], 201);
        } catch(\Exception $e){
            return response()->json([
                'message'=>'Oops! Terjadi kesalahan💀',
                'error'=>$e->getMessage(),
            ], 500);
        }
    }

    public function index(){
        $data = Journal::whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->latest()
        ->paginate(5);

        return response()->json([
            'message'=>'Journal Data',
            'data'=>$data->items(),
            'pagination'=>[
                'current_page'=>$data->currentPage(),
                'last_page'=>$data->lastPage(),
                'per_page'=>$data->perPage(),
                'total'=>$data->total(),
            ],
        ], 200);
    }

    public function show($slug){
        $data = Journal::where('slug', $slug)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->firstOrFail();

        $data->journal_file_url = asset('storage/journal/files/' . $data->journal_file);

        return response()->json([
            'message'=>'Journal berhasil dicari!',
            'data'=>$data,
        ], 200);
    }

   public function update(Request $request, $slug)
    {
        $data = Journal::where('slug', $slug)->firstOrFail();
        $this->authorize('manage jurnal');

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|unique:journals,title,' . $data->id,
            'journal_author' => 'sometimes|required|string',
            'publish_date' => 'sometimes|required|date',
            'doi' => 'sometimes|nullable|string',
            'journal_abstract' => 'sometimes|required|string',
            'journal_file' => 'nullable|mimes:pdf|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            if ($request->filled('title')) {
                $title = $request->input('title');

                if ($title !== $data->title) {
                    $baseSlug = Str::slug($title);
                    $newSlug = $baseSlug;
                    $count = 1;

                    while (
                        Journal::where('slug', $newSlug)
                            ->where('id', '!=', $data->id)
                            ->exists()
                    ) {
                        $newSlug = $baseSlug . '-' . $count++;
                    }

                    $data->slug = $newSlug;
                }

                $data->title = $title;
            }

            if ($request->filled('journal_author')) {
                $data->journal_author = $request->input('journal_author');
            }

            if ($request->filled('publish_date')) {
                $data->publish_date = $request->input('publish_date');
            }

            if ($request->filled('doi')) {
                $data->doi = $request->input('doi');
            }

            if ($request->filled('journal_abstract')) {
                $data->journal_abstract = $request->input('journal_abstract');
            }

            if ($request->hasFile('journal_file')) {
                if ($data->journal_file) {
                    Storage::disk('public')->delete('journal/files/' . $data->journal_file);
                }

                $filePath = $request->file('journal_file')
                    ->store('journal/files', 'public');

                $data->journal_file = basename($filePath);
            }

            $data->save();
            DB::commit();

            return response()->json([
                'message' => 'Journal Updated',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($slug){
        $this->authorize('manage jurnal');
        $data = Journal::where('slug', $slug)
            ->firstOrFail();
        try{
            DB::beginTransaction();
            Storage::disk('public')->delete('journal/files/' . $data->journal_file);
            $data->delete();
            DB::commit();
            return response()->json([
                'message'=>'Journal berhasil dihapus!',
            ], 200);
        } catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'message'=>'Oops! Terjadi kesalahan',
                'error'=>$e->getMessage(),
            ], 500);
        }
    }
}
