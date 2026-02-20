<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Dokumentasi API Methodly",
 *     version="1.0.0",
 *     description="Dokumentasi API untuk sistem Backend Methodly.",
 *     @OA\Contact(
 *         email="support@methodly.com"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Server API Methodly"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Masukkan token Bearer untuk akses API"
 * )
 * 
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(property="message", type="string", example="Operasi berhasil"),
 *     @OA\Property(property="data", type="object", nullable=true)
 * )
 */
abstract class Controller
{
    use \App\Traits\ApiResponse;
}
