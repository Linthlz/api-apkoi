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

        $request->validate([
            'title' => 'required|string',
            'tanggal' => 'required|string',
            'content' => 'required|string',
            'news_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'published_at' => 'required|date'
        ]);

        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title);

        // upload image
        $image = $request->file('news_image');
        $image->storeAs('news', $image->hashName(), 'public');

        $news = News::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'tanggal' => $request->tanggal,
            'news_image' => $image->hashName(),
            'content' => $request->content,
            'slug' => $slug,
            'publisher_id' => Auth::user()->id,
            'published_at' => $request->published_at,
        ]);

        return response()->json([
            'message' => 'News berhasil dibuat!',
            'data' => $news
        ], 201);
    }
}
