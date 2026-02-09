<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Manajemen User Admin", description="Endpoint admin untuk mengelola user dan role mereka")
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     summary="Daftar semua user",
     *     tags={"Manajemen User Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Daftar user berhasil diambil")
     * )
     */
    public function index()
    {
        return response()->json(User::with('roles', 'permissions')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/{id}/roles",
     *     summary="Sinkronisasi role ke user",
     *     tags={"Manajemen User Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="instructor"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role berhasil diperbarui")
     * )
     */
    public function syncRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'Role user berhasil diperbarui',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{id}",
     *     summary="Hapus user",
     *     tags={"Manajemen User Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="User berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
