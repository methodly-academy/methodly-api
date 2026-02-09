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
 */
abstract class Controller
{
    //
}
