<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'],
            'no_telepon' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:100', 'unique:pelanggan,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $customer = Pelanggan::create($data);

        return response()->json([
            'success' => true,
            'data' => $this->userData($customer, 'pelanggan'),
            'token' => $customer->createToken('mobile')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = strtolower($credentials['identifier']);
        $account = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? Pelanggan::where('email', $identifier)->first()
            : User::where('username', $identifier)->first();

        if (! $account || ! Hash::check($credentials['password'], $account->password)
            || ($account instanceof User && ! $account->is_active)) {
            throw ValidationException::withMessages([
                'identifier' => ['Email/username atau kata sandi tidak valid.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->userData($account, $account instanceof Pelanggan ? 'pelanggan' : $account->role),
            'token' => $account->createToken('mobile')->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $account = $request->user();
        $role = $account instanceof Pelanggan ? 'pelanggan' : $account->role;

        return response()->json(['success' => true, 'data' => $this->userData($account, $role)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['success' => true, 'message' => 'Berhasil keluar.']);
    }

    private function userData(Pelanggan|User $account, string $role): array
    {
        return [
            'id' => $account->getKey(),
            'id_pelanggan' => $account instanceof Pelanggan ? $account->getKey() : null,
            'id_user' => $account instanceof User ? $account->getKey() : null,
            'nama' => $account->nama,
            'email' => $account instanceof Pelanggan ? $account->email : null,
            'username' => $account instanceof User ? $account->username : null,
            'alamat' => $account instanceof Pelanggan ? $account->alamat : null,
            'no_telepon' => $account instanceof Pelanggan ? $account->no_telepon : null,
            'role' => $role,
        ];
    }
}
