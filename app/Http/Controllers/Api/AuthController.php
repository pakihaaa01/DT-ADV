<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        // Validasi input dari request
        $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/username atau password salah.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function loginGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function verifyTokenIdGoogle(Request $request): JsonResponse
    {
        $request->validate([
            'tokenId' => ['required', 'string'],
        ], [
            'tokenId.required' => 'Google ID Token wajib dikirim.',
        ]);

        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));

        try {
            // Verifikasi token ke server Google
            $payload = $client->verifyIdToken($request->input('tokenId'));

            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google ID Token tidak valid.',
                ], 401);
            }

            // Cari user berdasarkan email dari payload Google
            $user = User::where('email', $payload['email'])->first();

            if ($user) {
                // User sudah ada — simpan google_id jika belum ada
                if (!$user->google_id) {
                    $user->update(['google_id' => $payload['sub']]);
                }
            } else {
                // User belum ada — auto-register
                // Generate username dari bagian sebelum @ email
                $baseUsername = explode('@', $payload['email'])[0];
                $username = $baseUsername;
                $counter = 1;

                // Pastikan username unik dengan menambah angka jika perlu
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $user = User::create([
                    'username' => $username,
                    'email' => $payload['email'],
                    'google_id' => $payload['sub'],
                    'password' => Hash::make(Str::random(32)),
                ]);
            }

            // Hapus token lama, buat Sanctum token baru
            $user->tokens()->delete();
            $token = $user->createToken('google_auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login dengan Google berhasil.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}
