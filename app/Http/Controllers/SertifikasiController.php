<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sertifikasi;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SertifikasiController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $this->authorize('manage sertifikasi');

        $validator =Validator::make($request->all(),[
            'title' => 'required|string|unique:sertifikasis,title',
            'sertificate_owner' => 'required|string',
            'sertificate_code' => 'required|string',
            'tanggal' => 'required|string',
            'certificate_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'certificate_file' => 'required|mimes:pdf|max:4096',
            // 'published_at' => 'required|date'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        //Public ID And Slug
        $uuid = Str::uuid()->toString();
        $slug = Str::slug($request->title);

        // upload image
        $imagePath = $request->file('certificate_image')->store('sertifikasi/images', 'public');
        $filePath = $request->file('certificate_file')->store('sertifikasi/files', 'public');

        $sertifikasi = Sertifikasi::create([
            'public_id' => $uuid,
            'title' => $request->title,
            'sertificate_owner' => $request->sertificate_owner,
            'sertificate_code' => $request->sertificate_code,
            'sertificate_image' => basename($imagePath),
            'sertificate_file' => basename($filePath),
            'slug' => $slug,
            'publisher_id' => Auth::user()->id,
            'tanggal' => $request->tanggal,
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Certificate berhasil dibuat!',
            'data' => $sertifikasi
        ], 201);
        try {
        // Error Catch
        } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan',
            'error' => $e->getMessage()
        ], 500);
        }
    }
    public function index(){
        $this->authorize('manage sertifikasi');
         $data = Sertifikasi::whereNotNull('published_at')
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
        $data = Sertifikasi::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return response()->json([
            'message' => 'Certificate Fetched',
            'data' => $data
        ], 200);
    }
    public function update(Request $request, $slug)
    {
        $this->authorize('manage sertifikasi');
        $data = Sertifikasi::where('slug', $slug)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:sertifikasis,title,' . $data->id,
            'sertificate_owner' => 'required|string',
            'sertificate_code' => 'required|string',
            'tanggal' => 'required|date',
            'sertificate_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sertificate_file' => 'nullable|mimes:pdf|max:4096',
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
            $data->sertificate_owner = $request->sertificate_owner;
            $data->sertificate_code = $request->sertificate_code;
            $data->tanggal = $request->tanggal;

            if ($request->hasFile('sertificate_image')) {
                Storage::disk('public')->delete('sertifikasi/images/' . $data->sertificate_image);
                $imagePath = $request->file('sertificate_image')->store('sertifikasi/images', 'public');
                $data->sertificate_image = basename($imagePath);
            }

            if ($request->hasFile('sertificate_file')) {
                Storage::disk('public')->delete('sertifikasi/files/' . $data->sertificate_file);
                $filePath = $request->file('sertificate_file')->store('sertifikasi/files', 'public');
                $data->sertificate_file = basename($filePath);
            }

            $data->save();
            DB::commit();

            return response()->json([
                'message' => 'Certificate Updated',
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
        $this->authorize('manage sertifikasi');
        $data = Sertifikasi::where('slug', $slug)->firstOrFail();
        try {
            DB::beginTransaction();

            Storage::disk('public')->delete('sertifikasi/images/' . $data->sertificate_image);
            Storage::disk('public')->delete('sertifikasi/files/' . $data->sertificate_file);

            $data->delete();
            DB::commit();

            return response()->json([
                'message' => 'Certificate Deleted'
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
