<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesListing;
use App\Models\Agenda;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AgendaController extends Controller
{
    use AuthorizesRequests, HandlesListing;

    public const CATEGORIES = ['workshop', 'seminar', 'sertifikasi', 'organisasi'];

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'title' => "$required|string|max:255",
            'tanggal' => "$required|date_format:Y-m-d",
            'waktu' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'category' => 'nullable|in:' . implode(',', self::CATEGORIES),
            'location' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Daftar agenda, urut berdasarkan tanggal. Filter opsional: ?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function index(Request $request)
    {
        $data = Agenda::query()
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('tanggal', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('tanggal', '<=', $to))
            ->orderBy('tanggal')
            ->orderBy('waktu')
            ->paginate($this->perPage($request, 100));

        return $this->paginated('Agenda', $data);
    }

    public function show($slug)
    {
        return response()->json([
            'message' => 'Agenda Fetched',
            'data' => Agenda::where('slug', $slug)->firstOrFail(),
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('manage agenda');

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $agenda = Agenda::create([
            ...$validator->validated(),
            'category' => $request->input('category', 'workshop'),
            'public_id' => Str::uuid()->toString(),
            'slug' => $this->uniqueSlug(Agenda::class, $request->title . ' ' . $request->tanggal),
            'publisher_id' => Auth::id(),
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Agenda berhasil dibuat!',
            'data' => $agenda,
        ], 201);
    }

    public function update(Request $request, $slug)
    {
        $this->authorize('manage agenda');
        $agenda = Agenda::where('slug', $slug)->firstOrFail();

        $validator = Validator::make($request->all(), $this->rules(true));
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $agenda->fill($validator->validated());
        if ($agenda->isDirty(['title', 'tanggal'])) {
            $agenda->slug = $this->uniqueSlug(Agenda::class, $agenda->title . ' ' . $agenda->tanggal->format('Y-m-d'), $agenda->id);
        }
        $agenda->save();

        return response()->json([
            'message' => 'Agenda Updated',
            'data' => $agenda,
        ], 200);
    }

    public function destroy($slug)
    {
        $this->authorize('manage agenda');
        Agenda::where('slug', $slug)->firstOrFail()->delete();

        return response()->json([
            'message' => 'Agenda berhasil dihapus!',
        ], 200);
    }
}
