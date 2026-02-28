<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KurikulumController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $this->authorize('manage kurikulum');

        $request->validate([
            'title' => 'required|string',
            'tanggal' => 'required|string',
            'kurikulum_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'kurikulum_file' => 'required|mimes:pdf,doc,docx|max:4096',
            'published_at' => 'required|date'
        ]);

        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title);

        // upload image
        $image = $request->file('kurikulum_image');
        $image->storeAs('kurikulum/images', $image->hashName(), 'public');

        // upload file
        $file = $request->file('kurikulum_file');
        $file->storeAs('kurikulum/files', $file->hashName(), 'public');

        $kurikulum = Kurikulum::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'tanggal' => $request->tanggal,
            'kurikulum_image' => $image->hashName(),
            'kurikulum_file' => $file->hashName(),
            'slug' => $slug,
            'publisher_id' => Auth::user()->id,
            'published_at' => $request->published_at,
        ]);

        return response()->json([
            'message' => 'Kurikulum berhasil dibuat!',
            'data' => $kurikulum
        ], 201);
    }
}
