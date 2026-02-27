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
    $this->authorize('create kurikulum');

    $validated = $request->validate([
        'title'           => 'required|string|max:255',
        'tanggal'         => 'required|string',
        'kurikulum_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'kurikulum_file'  => 'nullable|file|max:5120',
    ]);

    if ($request->hasFile('kurikulum_image')) {
        $validated['kurikulum_image'] =
            $request->file('kurikulum_image')->store('kurikulum', 'public');
    }

    if ($request->hasFile('kurikulum_file')) {
        $validated['kurikulum_file'] =
            $request->file('kurikulum_file')->store('kurikulum', 'public');
    }

    $validated['public_id']    = Str::uuid();
    $validated['slug']         = Str::slug($validated['title']);
    $validated['publisher_id'] = Auth::id();
    $validated['published_at'] = now();

    $kurikulum = Kurikulum::create($validated);

    return response()->json([
        'message' => 'Kurikulum created successfully',
        'data'    => $kurikulum
    ], 201);
}
}
