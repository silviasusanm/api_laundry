<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Layanan::query();
        if (! in_array($request->user()?->role, ['kasir', 'pemilik'], true)) {
            $query->where('is_active', true);
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('nama_layanan')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'kasir' || $request->user()?->role === 'pemilik', 403);
        $service = Layanan::create($request->validate([
            'nama_layanan' => ['required', 'string', 'max:100'],
            'harga_per_unit' => ['required', 'numeric', 'min:0.01'],
            'satuan' => ['required', 'string', 'max:20'],
        ]));

        return response()->json(['success' => true, 'data' => $service], 201);
    }

    public function show(Layanan $layanan): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $layanan]);
    }

    public function update(Request $request, Layanan $layanan): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['kasir', 'pemilik'], true), 403);
        $layanan->update($request->validate([
            'nama_layanan' => ['sometimes', 'required', 'string', 'max:100'],
            'harga_per_unit' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'satuan' => ['sometimes', 'required', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ]));

        return response()->json(['success' => true, 'data' => $layanan->refresh()]);
    }

    public function destroy(Request $request, Layanan $layanan): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['kasir', 'pemilik'], true), 403);
        $layanan->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
