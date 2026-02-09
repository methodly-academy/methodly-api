<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     summary="Kirim tautan atur ulang kata sandi melalui email",
     *     tags={"Atur Ulang Kata Sandi"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com", description="Alamat email pengguna")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tautan atur ulang berhasil dikirim",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Tautan atur ulang berhasil dikirim"))
     *     ),
     *     @OA\Response(response=422, description="Kesalahan validasi data")
     * )
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/reset-password",
     *     summary="Atur ulang kata sandi pengguna",
     *     tags={"Atur Ulang Kata Sandi"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string", example="TOKEN_DISINI", description="Token dari email"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com", description="Alamat email"),
     *             @OA\Property(property="password", type="string", format="password", example="rahasia_baru123", description="Kata sandi baru"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="rahasia_baru123", description="Konfirmasi kata sandi baru")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kata sandi berhasil diatur ulang",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Kata sandi berhasil diatur ulang"))
     *     ),
     *     @OA\Response(response=422, description="Kesalahan validasi data")
     * )
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
