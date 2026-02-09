<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup awal untuk setiap test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Membuat role yang dibutuhkan (student) karena di AuthController ada penugasan role secara default
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'student']);
    }

    /**
     * Test pendaftaran pengguna baru.
     */
    public function test_user_can_register()
    {
        $payload = [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        // Mengirim request ke endpoint register v1
        $response = $this->postJson('/api/v1/register', $payload);

        // Memastikan status response 201 (Created)
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'access_token',
                'refresh_token',
                'token_type',
                'user'
            ])
            ->assertJson(['message' => 'Pengguna berhasil didaftarkan']);

        // Memastikan data tersimpan di database
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Santoso',
        ]);
    }

    /**
     * Test login pengguna dengan kredensial yang benar.
     */
    public function test_user_can_login_with_correct_credentials()
    {
        // Membuat user percobaan
        $user = User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'budi@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'refresh_token', 'user'])
            ->assertJson(['message' => 'Login berhasil']);
    }

    /**
     * Test login gagal karena email belum terdaftar.
     */
    public function test_login_fails_if_email_not_registered()
    {
        $payload = [
            'email' => 'unregistered@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment(['email' => ['Email belum terdaftar.']]);
    }

    /**
     * Test login gagal karena password salah.
     */
    public function test_login_fails_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'budi@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment(['password' => ['Password salah.']]);
    }

    /**
     * Test mengambil data profil user yang sedang login.
     */
    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        
        // Bertindak sebagai user yang sudah terautentikasi (menggunakan Sanctum)
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('email', $user->email);
    }

    /**
     * Test logout pengguna.
     */
    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        
        // Membuat token untuk user tersebut
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Berhasil keluar']);

        // Memastikan token sudah dihapus di database
        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test pengguna bisa memperbarui token menggunakan refresh token.
     */
    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        
        // Buat refresh token manual
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], now()->addDays(7))->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $refreshToken)
            ->postJson('/api/v1/refresh-token');

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'refresh_token', 'token_type']);

        // Memastikan refresh token lama dihapus (karena kita pakai rotasi di controller)
        // Awalnya ada 1 (refresh_token manual), lalu panggil API (hapus 1 lama, tambah 2 baru)
        // Jadi total token harusnya 2 (access_token baru & refresh_token baru)
        $this->assertCount(2, $user->fresh()->tokens);
    }

    /**
     * Test refresh token gagal jika menggunakan access token biasa.
     */
    public function test_refresh_token_fails_with_access_token()
    {
        $user = User::factory()->create();
        
        // Buat access token biasa (tanpa ability refresh-token)
        $accessToken = $user->createToken('access_token', ['access-api'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $accessToken)
            ->postJson('/api/v1/refresh-token');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Token tidak valid untuk pembaruan']);
    }
}
