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
    $this->authorize('create sertifikasi');

    $validated = $request->validate([
        'title'             => 'required|string|max:255',
        'sertificate_owner' => 'nullable|string|max:255',
        'sertificate_code'  => 'nullable|string|max:255',
        'sertificate_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'sertificate_file'  => 'nullable|file|max:5120',
        'tanggal'           => 'required|string',
    ]);

    if ($request->hasFile('sertificate_image')) {
        $validated['sertificate_image'] =
            $request->file('sertificate_image')->store('sertifikasi', 'public');
    }

    if ($request->hasFile('sertificate_file')) {
        $validated['sertificate_file'] =
            $request->file('sertificate_file')->store('sertifikasi', 'public');
    }

    $validated['public_id']    = Str::uuid();
    $validated['slug']         = Str::slug($validated['title']);
    $validated['publisher_id'] = Auth::id();
    $validated['published_at'] = now();

    $sertifikasi = Sertifikasi::create($validated);

    return response()->json([
        'message' => 'Sertifikasi created successfully',
        'data'    => $sertifikasi
    ], 201);
}
}
