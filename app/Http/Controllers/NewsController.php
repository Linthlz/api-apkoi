<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    // 'public_id',
    // 'title',
    // 'tanggal',
    // 'news_image',
    // 'content',
    // 'slug',
    // 'publisher_id',
    // 'published_at',
         //UNIV UPTS
            // 'manage news',
            // 'manage kurikulum',
            // 'manage profile',
            // 'manage certificate',
            // 'manage jurnal',

    use AuthorizesRequests;
    public function store(Request $request)
    {
        $this->authorize('manage news');

        $validator =Validator::make($request->all(),[
            'title' => 'required|string|unique:news,title',
            'tanggal' => 'required|string',
            'content' => 'required|string',
            'news_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'published_at' => 'required|date'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        //Public ID And Slug
        try{    
        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title);

        // upload image
        $imagePath = $request->file('news_image')->store('news', 'public');

        $news = News::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'tanggal' => $request->tanggal,
            'news_image' => basename($imagePath),
            'content' => $request->content,
            'slug' => $slug,
            'publisher_id' => Auth::user()->id,
            'published_at' => now()
        ]);

        return response()->json([
            'message' => 'News berhasil dibuat!',
            'data' => $news
        ], 201);
        // Error Catch
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
            }
    }


    public function index(){
         $data = News::whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->latest()
        ->paginate(5);

        return response()->json([
            'message' => 'News',
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ]
        ], 200);
    }
    public function show($slug)
    {
        $data = News::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();
            $data->news_image_url = asset('storage/news/' . $data->news_image);
        return response()->json([
            'message' => 'News Fetched',
            'data' => $data
        ], 200);
    }
public function update(Request $request, $slug){
    $data = News::where('slug', $slug)->firstOrFail();
    $this->authorize('manage news');

    $validator = Validator::make($request->all(), [
        'title' => 'sometimes|required|string|unique:news,title,' . $data->id,
        'tanggal' => 'sometimes|required|date',
        'content' => 'sometimes|required|string',
        'news_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }
    try {
        DB::beginTransaction();

        if ($request->has('title')) {
            $title = $request->input('title');

            if ($title !== $data->title) {
                $baseSlug = Str::slug($title);
                $newSlug = $baseSlug;
                $count = 1;

                while (
                   News::where('slug', $newSlug)
                        ->where('id', '!=', $data->id)
                        ->exists()
                ) {
                    $newSlug = $baseSlug . '-' . $count++;
                }

                $data->slug = $newSlug;
            }

            $data->title = $title;
        }

        if ($request->has('tanggal')) {
            $data->tanggal = $request->input('tanggal');
        }

        if ($request->has('content')) {
            $data->content = $request->input('content');
        }

        if ($request->hasFile('news_image')) {
            if ($data->news_image) {
                Storage::disk('public')->delete('news/' . $data->news_image);
            }
            $imagePath = $request->file('news_image')->store('news', 'public');
            $data->news_image = basename($imagePath);
        }

        $data->save();
        DB::commit();

        return response()->json([
            'message' => 'News Updated',
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
    public function destroy($slug)
    {
        $data = News::where('slug', $slug)->firstOrFail();
         $this->authorize('manage news');
        try {
            DB::beginTransaction();
            Storage::disk('public')->delete('news/' . $data->news_image);
            $data->delete();
            DB::commit();
            return response()->json([
                'message' => 'News Deleted'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
