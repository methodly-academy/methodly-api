<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(name="Manajemen Role", description="Endpoint untuk mengelola role dan izin")
 */
class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/roles",
     *     summary="Daftar semua role",
     *     tags={"Manajemen Role"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Daftar role berhasil diambil")
     * )
     */
    public function index()
    {
        return response()->json(Role::with('permissions')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/roles",
     *     summary="Buat role baru",
     *     tags={"Manajemen Role"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="editor")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Role berhasil dibuat")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json([
            'message' => 'Role berhasil dibuat',
            'role' => $role
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/roles/{id}",
     *     summary="Detail role",
     *     tags={"Manajemen Role"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail role berhasil diambil")
     * )
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/roles/{id}",
     *     summary="Update nama role",
     *     tags={"Manajemen Role"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(@OA\Property(property="name", type="string", example="editor-senior"))
     *     ),
     *     @OA\Response(response=200, description="Role berhasil diupdate")
     * )
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
        ]);

        $role->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Role berhasil diupdate',
            'role' => $role
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/roles/{id}",
     *     summary="Hapus role",
     *     tags={"Manajemen Role"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Role berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Role berhasil dihapus']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/roles/{id}/permissions",
     *     summary="Sinkronisasi izin ke role",
     *     description="Menghapus semua izin lama dan menggantinya dengan daftar izin yang baru",
     *     tags={"Manajemen Role"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="manage users"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Izin berhasil disinkronisasi")
     * )
     */
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Izin berhasil disinkronisasi ke role',
            'role' => $role->load('permissions')
        ]);
    }
}
