<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sertifikasi;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SertifikasiController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $this->authorize('manage sertifikasi');

        $request->validate([
            'title' => 'required|string',
            'sertificate_owner' => 'required|string',
            'sertificate_code' => 'required|string',
            'tanggal' => 'required|string',
            'sertificate_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'sertificate_file' => 'required|mimes:pdf|max:4096',
            'published_at' => 'required|date'
        ]);

        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title);

        // upload image
        $image = $request->file('sertificate_image');
        $image->storeAs('sertifikasi/images', $image->hashName(), 'public');

        // upload file
        $file = $request->file('sertificate_file');
        $file->storeAs('sertifikasi/files', $file->hashName(), 'public');

        $sertifikasi = Sertifikasi::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'sertificate_owner' => $request->sertificate_owner,
            'sertificate_code' => $request->sertificate_code,
            'sertificate_image' => $image->hashName(),
            'sertificate_file' => $file->hashName(),
            'slug' => $slug,
            'publisher_id' => Auth::user()->id,
            'tanggal' => $request->tanggal,
            'published_at' => $request->published_at,
        ]);

        return response()->json([
            'message' => 'Sertifikasi berhasil dibuat!',
            'data' => $sertifikasi
        ], 201);
    }

}
