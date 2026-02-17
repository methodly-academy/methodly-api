<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Daftar pengguna baru",
     *     tags={"Autentikasi"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Budi Santoso", description="Nama lengkap pengguna"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com", description="Alamat email unik"),
     *             @OA\Property(property="password", type="string", format="password", example="rahasia123", description="Kata sandi minimal 8 karakter"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="rahasia123", description="Konfirmasi kata sandi"),
     *             @OA\Property(property="role", type="string", enum={"student", "admin"}, example="student", description="Peran pengguna")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pengguna berhasil didaftarkan",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengguna berhasil didaftarkan"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Kesalahan validasi data")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['sometimes', 'string', 'in:student,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = $request->input('role', 'student');
        $user->assignRole($role);

        $accessToken = $user->createToken('access_token', ['access-api'], now()->addMinutes(15))->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'Pengguna berhasil didaftarkan',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'user' => $user->load('roles', 'permissions'),
        ], 201)->cookie('refresh_token', $refreshToken, 60 * 24 * 7, null, null, true, true);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Masuk ke sistem",
     *     tags={"Autentikasi"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="rahasia123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login berhasil"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Kredensial tidak valid (Email belum terdaftar atau kata sandi salah)"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Kesalahan validasi data atau akun tidak ditemukan"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Email belum terdaftar.'],
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password salah.'],
            ]);
        }

        $accessToken = $user->createToken('access_token', ['access-api'], now()->addMinutes(15))->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'user' => $user->load('roles', 'permissions'), 
        ])->cookie('refresh_token', $refreshToken, 60 * 24 * 7, null, null, true, true);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Keluar dari sistem",
     *     tags={"Autentikasi"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil keluar",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Berhasil keluar"))
     *     ),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar'])
            ->withoutCookie('refresh_token');
    }

     /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     summary="Dapatkan profil pengguna yang sedang masuk",
     *     description="Endpoint ini memerlukan token Bearer yang valid. Pastikan Anda telah melakukan login dan memasukkan token melalui tombol 'Authorize' di atas.",
     *     tags={"Autentikasi"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data pengguna yang terautentikasi",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Tidak terautentikasi")
     * )
     */
    public function user(Request $request)
    {
        return response()->json(
            $request->user()->load('roles.permissions')
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/refresh-token",
     *     summary="Perbarui token akses",
     *     description="Gunakan refresh_token untuk mendapatkan access_token yang baru.",
     *     tags={"Autentikasi"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token berhasil diperbarui. access_token baru di body, refresh_token baru di Set-Cookie.",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Refresh token tidak valid atau sudah kedaluwarsa")
     * )
     */
    public function refreshToken(Request $request)
    {
        $token = $request->cookie('refresh_token') ?? $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Refresh token tidak ditemukan'], 401);
        }

        $accessTokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$accessTokenModel || !$accessTokenModel->tokenable || !$accessTokenModel->can('refresh-token')) {
            return response()->json(['message' => 'Token tidak valid untuk pembaruan'], 401);
        }

        if ($accessTokenModel->expires_at && $accessTokenModel->expires_at->isPast()) {
            return response()->json(['message' => 'Refresh token sudah kedaluwarsa'], 401);
        }

        $user = $accessTokenModel->tokenable;

        // Hapus refresh token lama (rotasi)
        $accessTokenModel->delete();

        $newAccessToken = $user->createToken('access_token', ['access-api'], now()->addMinutes(15))->plainTextToken;
        $newRefreshToken = $user->createToken('refresh_token', ['refresh-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
        ])->cookie('refresh_token', $newRefreshToken, 60 * 24 * 7, null, null, true, true);
    }
}
