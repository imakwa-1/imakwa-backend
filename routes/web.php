<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Temporary route to seed admin - DELETE AFTER USE
Route::get('/setup-admin-urgent', function () {
    try {
        $admin = User::firstOrCreate(
            ['email' => 'admin@imakwa.com'],
            [
                'name' => 'Imakwa Admin',
                'password' => Hash::make('Admin@2026!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin user created successfully!',
            'email' => 'admin@imakwa.com',
            'password' => 'Admin@2026!',
            'login_url' => url('/admin')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

require __DIR__.'/auth.php';
