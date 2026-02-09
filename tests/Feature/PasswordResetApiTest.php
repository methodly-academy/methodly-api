<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test pengiriman email tautan reset password.
     */
    public function test_user_can_request_password_reset_link()
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);

        $payload = ['email' => 'budi@example.com'];

        $response = $this->postJson('/api/v1/forgot-password', $payload);

        // Catatan: Di environment testing, email tidak benar-benar dikirim melainkan hanya status sukses
        $response->assertStatus(200);
        
        // Memastikan token sudah terbuat di tabel password_reset_tokens
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'budi@example.com',
        ]);
    }

    /**
     * Test reset password dengan token yang valid.
     */
    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        // Membuat token reset password secara manual melalui broker Laravel
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'budi@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->postJson('/api/v1/reset-password', $payload);

        $response->assertStatus(200);

        // Memastikan password sudah berubah di database
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /**
     * Test reset password gagal karena token tidak valid.
     */
    public function test_password_reset_fails_with_invalid_token()
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $payload = [
            'token' => 'invalid-token',
            'email' => 'budi@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->postJson('/api/v1/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test keamanan: Password gagal diubah jika konfirmasi password tidak cocok.
     */
    public function test_password_reset_fails_if_passwords_do_not_match()
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'budi@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->postJson('/api/v1/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
