<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{
    public function customers(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['kasir', 'pemilik'], true), 403);

        return response()->json(['success' => true, 'data' => Pelanggan::orderBy('nama')->paginate(30)]);
    }

    public function updateCustomer(Request $request, Pelanggan $pelanggan): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['kasir', 'pemilik'], true), 403);
        $pelanggan->update($request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:100'],
            'alamat' => ['sometimes', 'required', 'string', 'max:255'],
            'no_telepon' => ['sometimes', 'required', 'string', 'max:15'],
            'email' => ['sometimes', 'required', 'email', 'max:100', Rule::unique('pelanggan')->ignore($pelanggan->getKey(), 'id_pelanggan')],
        ]));

        return response()->json(['success' => true, 'data' => $pelanggan->refresh()]);
    }

    public function deleteCustomer(Request $request, Pelanggan $pelanggan): JsonResponse
    {
        abort_unless($request->user()?->role === 'pemilik', 403);
        abort_if($pelanggan->pesanan()->exists(), 409, 'Pelanggan dengan riwayat pesanan tidak dapat dihapus.');
        $pelanggan->delete();

        return response()->json(['success' => true]);
    }

    public function users(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'pemilik', 403);

        return response()->json(['success' => true, 'data' => User::orderBy('nama')->get()]);
    }

    public function createUser(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'pemilik', 403);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:user,username'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['kasir', 'pemilik'])],
        ]);
        $user = User::create([...$data, 'password' => Hash::make($data['password']), 'is_active' => true]);

        return response()->json(['success' => true, 'data' => $user], 201);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->role === 'pemilik', 403);
        $data = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:100'],
            'role' => ['sometimes', 'required', Rule::in(['kasir', 'pemilik'])],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
        ]);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        if (($data['is_active'] ?? true) === false || isset($data['password'])) {
            $user->tokens()->delete();
        }

        return response()->json(['success' => true, 'data' => $user->refresh()]);
    }

    public function report(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'pemilik', 403);
        $period = $request->validate(['periode' => ['sometimes', Rule::in(['harian', 'mingguan', 'bulanan'])]])['periode'] ?? 'harian';
        $start = now()->subDays(29)->startOfDay();
        $payments = Pembayaran::where('tanggal_bayar', '>=', $start)->orderBy('tanggal_bayar')->get();
        $key = match ($period) {
            'mingguan' => fn ($date) => $date->copy()->startOfWeek()->toDateString(),
            'bulanan' => fn ($date) => $date->format('Y-m'),
            default => fn ($date) => $date->toDateString(),
        };
        $summary = $payments->groupBy(fn ($payment) => $key($payment->tanggal_bayar))
            ->map(fn ($rows, $label) => ['periode' => $label, 'jumlah_transaksi' => $rows->count(), 'pendapatan' => (float) $rows->sum('jumlah_bayar')])
            ->values();

        return response()->json(['success' => true, 'data' => ['periode' => $period, 'ringkasan' => $summary, 'total' => (float) $payments->sum('jumlah_bayar')]]);
    }

    public function notifications(Request $request): JsonResponse
    {
        abort_unless($request->user() instanceof Pelanggan, 403);
        $notifications = Notifikasi::where('id_pelanggan', $request->user()->getKey())->latest()->paginate(30);

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    public function markNotificationRead(Request $request, Notifikasi $notifikasi): JsonResponse
    {
        abort_unless($request->user() instanceof Pelanggan && $notifikasi->id_pelanggan === $request->user()->getKey(), 403);
        $notifikasi->update(['dibaca' => true]);

        return response()->json(['success' => true, 'data' => $notifikasi->refresh()]);
    }
}
