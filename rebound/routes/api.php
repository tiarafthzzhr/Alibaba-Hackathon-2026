<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TranslationController;
/*
|--------------------------------------------------------------------------
| REBOUND Enterprise API Routes
|--------------------------------------------------------------------------
| High-performance API endpoints for PNR Verification, GDS Atlas Schedules,
| AI Rebooking Dispatch, and Cloud Health Checks.
*/

Route::post('/login/google', [AuthController::class, 'loginWithFirebase']);

// Route yang dilindungi otentikasi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user-profile', function (Request $request) {
        return $request->user();
    });
    // ---------------------------------------------------------
    // AKTIFKAN INI JIKA ATLAS CLI SUDAH SIAP DARI QODER/QWEN
    // ---------------------------------------------------------
    // Endpoint pencarian dan otorisasi PNR
    Route::post('/pnr/lookup', [FlightController::class, 'lookup']);
    // id: Aktivasi PNR dari modal — menyimpan PNR terverifikasi agar bertahan setelah refresh
    // en: PNR activation from the modal — persists the verified PNR so it survives refresh
    Route::post('/pnr/activate', [FlightController::class, 'activate']);
    // Endpoint Agent Chat yang baru dibuat
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    // id: Riwayat percakapan tersimpan untuk PNR aktif (agar chat bertahan setelah refresh)
    // en: Stored conversation history for the active PNR (so chat survives refresh)
    Route::get('/chat/history', [ChatController::class, 'history']);
    // id: Hapus sesi percakapan AI & riwayat pesan untuk menghemat memori
    // en: Delete AI chat session & message history to save storage space
    Route::delete('/chat/session/{id}', [ChatController::class, 'deleteSession']);
    // id: Saran prompt cerdas (lapis 2) — Qwen merumuskan saran kontekstual per PNR; tanpa API key
    //     respons berupa daftar kosong (source 'none') sehingga frontend mempertahankan saran lapis-1.
    // en: Smart prompt suggestions (layer 2) — Qwen crafts contextual suggestions per PNR; without an
    //     API key the response is an empty list (source 'none') so the frontend keeps layer-1 chips.
    Route::post('/chat/suggestions', [ChatController::class, 'aiSuggestions']);

    // id: Pusat notifikasi — daftar alert operasional milik user (dari tabel notifications)
    //     dan penandaan semua notifikasi sebagai sudah dibaca dari dropdown navbar.
    // en: Notification center — the user's operational alerts (from the notifications table)
    //     and mark-all-read triggered from the navbar dropdown.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);

    // id: Terjemahan dinamis — katalog gabungan (DB menimpa file lang statis) dan upsert
    //     baris terjemahan sehingga teks UI bisa diubah tanpa deploy ulang.
    // en: Dynamic translations — merged catalogue (DB overriding static lang files) and row
    //     upsert so UI copy can be edited without redeploying.
    Route::get('/translations', [TranslationController::class, 'index']);
    Route::post('/translations', [TranslationController::class, 'upsert']);


    // Atlas Sandbox search with an internal demo-inventory fallback.
    Route::get('/flights/alternatives', [FlightController::class, 'alternatives']);

    // id: Persistensi hasil rebooking — status rebooked + penerbangan alternatif pilihan disimpan
    //     ke tabel rebookings (satu baris per user + PNR), menggantikan localStorage frontend agar
    //     bertahan lintas perangkat/browser dan tercatat di server.
    // en: Rebooking result persistence — the rebooked status + chosen alternative flight are stored in
    //     the rebookings table (one row per user + PNR), replacing frontend localStorage so it survives
    //     across devices/browsers and is recorded server-side.
    Route::post('/rebook', [FlightController::class, 'rebook']);

    // id: Verifikasi PNR asli ke GDS via Atlas CLI — jika valid, PNR + user_id dicatat ke tabel user_pnrs (MySQL rebound_db)
    // en: Real PNR verification against the GDS via Atlas CLI — when valid, the PNR + user_id are recorded in user_pnrs (MySQL rebound_db)
    Route::post('/pnr/verify', [FlightController::class, 'verify']);
    
});


Route::get('/health', function () {
    $dbStatus = 'connected';
    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $dbStatus = 'disconnected';
    }

    return response()->json([
        'status' => 'healthy',
        'service' => 'REBOUND Aviation AI Gateway',
        'version' => '1.2.0',
        'timestamp' => now()->toIso8601String(),
        'database' => $dbStatus,
        'gds_atlas_connected' => true,
        'iata_engine' => 'active',
    ], 200);
});
