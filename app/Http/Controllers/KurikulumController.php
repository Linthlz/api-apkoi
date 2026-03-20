<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KurikulumController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
         $this->authorize('manage kurikulum');

        $validator =Validator::make($request->all(),[
            'title' => 'required|string',
            'tanggal' => 'required|string',
            'kurikulum_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'kurikulum_file' => 'required|mimes:pdf,doc,docx|max:4096',
            'published_at' => 'required|date'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        //Public ID And Slug
        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title). '-'. time();

        // upload image
        $imagePath = $request->file('kurikulum_images')->store('kurikulum/images', 'public');
        $filePath = $request->file('kurikulum_file')->store('kurikulum/files', 'public');

        $kurikulum = Kurikulum::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'tanggal' => $request->tanggal,
            'kurikulum_image' => basename($imagePath),
            'kurikulum_file' => basename($filePath),
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
