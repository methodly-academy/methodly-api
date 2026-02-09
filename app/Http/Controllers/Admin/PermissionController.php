<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(name="Manajemen Izin", description="Endpoint untuk mengelola izin (permissions)")
 */
class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/permissions",
     *     summary="Daftar semua izin",
     *     tags={"Manajemen Izin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Daftar izin berhasil diambil")
     * )
     */
    public function index()
    {
        return response()->json(Permission::all());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/permissions",
     *     summary="Buat izin baru",
     *     tags={"Manajemen Izin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="delete articles")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Izin berhasil dibuat")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json([
            'message' => 'Izin berhasil dibuat',
            'permission' => $permission
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/permissions/{id}",
     *     summary="Hapus izin",
     *     tags={"Manajemen Izin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Izin berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Izin berhasil dihapus']);
    }
}
