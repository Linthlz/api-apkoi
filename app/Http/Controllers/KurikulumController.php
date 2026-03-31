<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KurikulumController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
         $this->authorize('manage kurikulum');

        $validator =Validator::make($request->all(),[
            'title' => 'required|string|unique:kurikulums,title',
            'tanggal' => 'required|string',
            // 'kurikulum_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'kurikulum_file' => 'required|mimes:pdf,doc,docx|max:4096',
            // 'published_at' => 'required|date'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        try {
        //Public ID And Slug
        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title);

        // upload image
        // $imagePath = $request->file('kurikulum_image')->store('kurikulum/images', 'public');
        // $imagePath = $request->file('kurikulum_image')->store('kurikulum/images', 'public');
        $filePath = $request->file('kurikulum_file')->store('kurikulum/files', 'public');

        $kurikulum = Kurikulum::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'tanggal' => $request->tanggal,
            // 'kurikulum_image' => basename($imagePath),
            'kurikulum_file' => basename($filePath),
            'slug' => $slug,
            'publisher_id' => Auth::user()->id,
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Kurikulum berhasil dibuat!',
            'data' => $kurikulum
        ], 201);
        // error catching
        } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan',
            'error' => $e->getMessage()
        ], 500);
        }
    }

     public function index(){
         $data = Kurikulum::whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->latest()
        ->paginate(5);

        return response()->json([
            'message' => 'Kurikulum',
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
        $data = Kurikulum::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

            $data->kurikulum_image_url = asset('storage/kurikulum/images/' . $data->kurikulum_image);
            $data->kurikulum_file_url = asset('storage/kurikulum/files/' . $data->kurikulum_file);

        return response()->json([
            'message' => 'Kurikulum Fetched',
            'data' => $data
        ], 200);
    }
    //Note, Use slug not id
    public function update(Request $request, $slug)
    {
        $data = Kurikulum::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $data);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:kurikulums,title,' . $data->id,
            'tanggal' => 'required|date',
            'kurikulum_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kurikulum_file' => 'nullable|mimes:pdf,doc,docx|max:4096',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try {
            DB::beginTransaction();
            if ($request->title !== $data->title) {
                $data->slug = Str::slug($request->title);
            }
            $data->title = $request->title;
            $data->tanggal = $request->tanggal;
            if ($request->hasFile('kurikulum_image')) {
                Storage::disk('public')->delete('kurikulum/images/' . $data->kurikulum_image);
                $imagePath = $request->file('kurikulum_image')->store('kurikulum/images', 'public');
                $data->kurikulum_image = basename($imagePath);
            }
            if ($request->hasFile('kurikulum_file')) {
                Storage::disk('public')->delete('kurikulum/files/' . $data->kurikulum_file);
                $filePath = $request->file('kurikulum_file')->store('kurikulum/files', 'public');
                $data->kurikulum_file = basename($filePath);
            }
            $data->save();
            DB::commit();
            return response()->json([
                'message' => 'Kurikulum updated'
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
        $data = Kurikulum::where('slug', $slug)->firstOrFail();
        $this->authorize('delete', $data);
        try {
            DB::beginTransaction();
            Storage::disk('public')->delete('kurikulum/images/' . $data->kurikulum_image);
            Storage::disk('public')->delete('kurikulum/files/' . $data->kurikulum_file);
            $data->delete();
            DB::commit();
            return response()->json([
                'message' => 'Kurikulum berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
