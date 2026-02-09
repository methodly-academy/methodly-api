<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User Management
    require __DIR__ . '/admin/users.php';

    // Role Management
    require __DIR__ . '/admin/roles.php';

    // Permission Management
    require __DIR__ . '/admin/permissions.php';
});
