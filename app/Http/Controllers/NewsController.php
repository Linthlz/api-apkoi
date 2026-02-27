<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    $validated = $request->validate([
        'title'      => 'required|string|max:255',
        'tanggal'    => 'required|string',
        'content'    => 'required|string',
        'news_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($request->hasFile('news_image')) {
        $validated['news_image'] =
            $request->file('news_image')->store('news', 'public');
    }

    $validated['public_id']    = Str::uuid();
    $validated['slug']         = Str::slug($validated['title']);
    $validated['publisher_id'] = Auth::id();
    $validated['published_at'] = now();

    $news = News::create($validated);

    return response()->json([
        'message' => 'News created successfully',
        'data'    => $news
    ], 201);
}
}
