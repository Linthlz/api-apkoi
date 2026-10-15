<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
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
    use AuthorizesRequests, HandlesListing;

    // "title" = nama program/kegiatan sertifikat (boleh sama untuk banyak penerima)
    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'title' => "$required|string|max:255",
            'sertificate_owner' => "$required|string|max:255",
            'sertificate_code' => "$required|string|max:255",
            'tanggal' => "$required|date",
            'institution' => 'nullable|string|max:255',
            'sertificate_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sertificate_file' => ($partial ? 'nullable' : 'required') . '|mimes:pdf|max:4096',
        ];
    }

    // Terima juga nama field lama "certificate_*" yang dulu dipakai endpoint update
    private function normalizeFileFields(Request $request): void
    {
        foreach (['image', 'file'] as $kind) {
            if (!$request->hasFile("sertificate_$kind") && $request->hasFile("certificate_$kind")) {
                $request->files->set("sertificate_$kind", $request->file("certificate_$kind"));
            }
        }
    }

    private function withUrls(Sertifikasi $item): Sertifikasi
    {
        $item->sertificate_image_url = $item->sertificate_image ? asset('storage/sertifikasi/images/' . $item->sertificate_image) : null;
        $item->sertificate_file_url = $item->sertificate_file ? asset('storage/sertifikasi/files/' . $item->sertificate_file) : null;

        return $item;
    }

    public function store(Request $request)
    {
        $this->authorize('manage certificate');
        $this->normalizeFileFields($request);

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $imagePath = $request->hasFile('sertificate_image')
                ? $request->file('sertificate_image')->store('sertifikasi/images', 'public')
                : null;
            $filePath = $request->file('sertificate_file')->store('sertifikasi/files', 'public');

            $sertifikasi = Sertifikasi::create([
                'public_id' => Str::uuid()->toString(),
                'title' => $request->title,
                'sertificate_owner' => $request->sertificate_owner,
                'sertificate_code' => $request->sertificate_code,
                'institution' => $request->institution,
                'sertificate_image' => $imagePath ? basename($imagePath) : null,
                'sertificate_file' => basename($filePath),
                'slug' => $this->uniqueSlug(Sertifikasi::class, $request->sertificate_code . ' ' . $request->sertificate_owner),
                'publisher_id' => Auth::user()->id,
                'tanggal' => $request->tanggal,
                'published_at' => now(),
            ]);

            return response()->json([
                'message' => 'Certificate berhasil dibuat!',
                'data' => $this->withUrls($sertifikasi),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $data = Sertifikasi::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest()
            ->paginate($this->perPage($request));

        return $this->paginated('Sertifikasi', $data, fn ($item) => $this->withUrls($item));
    }

    public function show($slug)
    {
        $data = Sertifikasi::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return response()->json([
            'message' => 'Certificate Fetched',
            'data' => $this->withUrls($data),
        ], 200);
    }

    public function update(Request $request, $slug)
    {
        $data = Sertifikasi::where('slug', $slug)->firstOrFail();
        $this->authorize('manage certificate');
        $this->normalizeFileFields($request);

        $validator = Validator::make($request->all(), $this->rules(true));
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $data->fill($request->only(['title', 'sertificate_owner', 'sertificate_code', 'tanggal', 'institution']));

            if ($request->hasFile('sertificate_image')) {
                if ($data->sertificate_image) {
                    Storage::disk('public')->delete('sertifikasi/images/' . $data->sertificate_image);
                }
                $data->sertificate_image = basename($request->file('sertificate_image')->store('sertifikasi/images', 'public'));
            }
            if ($request->hasFile('sertificate_file')) {
                if ($data->sertificate_file) {
                    Storage::disk('public')->delete('sertifikasi/files/' . $data->sertificate_file);
                }
                $data->sertificate_file = basename($request->file('sertificate_file')->store('sertifikasi/files', 'public'));
            }
            if ($data->isDirty(['sertificate_code', 'sertificate_owner'])) {
                $data->slug = $this->uniqueSlug(Sertifikasi::class, $data->sertificate_code . ' ' . $data->sertificate_owner, $data->id);
            }

            $data->save();
            DB::commit();

            return response()->json([
                'message' => 'Certificate Updated',
                'data' => $this->withUrls($data),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($slug)
    {
        $data = Sertifikasi::where('slug', $slug)->firstOrFail();
        $this->authorize('manage certificate');

        try {
            DB::beginTransaction();

            if ($data->sertificate_image) {
                Storage::disk('public')->delete('sertifikasi/images/' . $data->sertificate_image);
            }
            if ($data->sertificate_file) {
                Storage::disk('public')->delete('sertifikasi/files/' . $data->sertificate_file);
            }

            $data->delete();
            DB::commit();

            return response()->json([
                'message' => 'Certificate Deleted',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ViewSertifikasiFile($filename)
    {
        $filename = basename($filename);
        $filePath = storage_path('app/public/sertifikasi/files/' . $filename);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
