<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// id: Rute Autentikasi Pengguna (Login & Register Form) — hanya untuk guest,
//     user yang sudah login otomatis dialihkan ke dashboard oleh middleware 'guest'
// en: User Authentication Routes (Login & Register Form) — guests only,
//     authenticated users are redirected to the dashboard by the 'guest' middleware
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
});

// id: Autentikasi Firebase (Google Sign-In & Email/Password) - memverifikasi ID Token di server
// en: Firebase Authentication (Google Sign-In & Email/Password) - verifies ID Token on server
Route::post('/auth/firebase', [AuthController::class, 'loginWithFirebase'])->name('auth.firebase');

// id: Pengganti Bahasa (ID / EN) dengan persistensi sesi & respons JSON untuk AJAX
// en: Language Switcher (ID / EN) with session persistence & JSON response for AJAX
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['id', 'en'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    if (request()->wantsJson()) {
        return response()->json([
            'status' => 'success',
            'locale' => $locale,
            'messages' => trans('messages', [], $locale)
        ]);
    }
    return redirect()->back();
})->name('locale.switch');

use App\Models\AgentChatSession;
use App\Models\MockGdsBooking;
use App\Models\Rebooking;
use App\Models\Translation;

// id: Dashboard Utama & Asisten Penerbangan REBOUND (Wajib Login)
// en: Protected Dashboard & REBOUND Flight Assistant (Authentication Required)
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        $user = auth()->user();

        // id: Utamakan PNR dari sesi chat AI paling terbaru (berdasarkan updated_at)
        // en: Prioritize PNR from the user's most recent AI chat session (based on updated_at)
        $latestChatSession = AgentChatSession::where('user_id', $user->id)->latest('updated_at')->first();

        // Ambil PNR berstatus 'active' milik user sebagai cadangan jika belum ada chat
        $activePnr = $user->pnrs()->where('status', 'active')->first();

        // Kode PNR aktif utama yang akan langsung dibuka saat pertama kali dimuat/login
        $activePnrCode = $latestChatSession?->pnr_code ?? $activePnr?->pnr_code;

        // id: Daftar seluruh PNR asli milik user dari database — dipakai modal aktivasi
        //     untuk menampilkan tiket nyata, menggantikan skenario uji coba statis.
        // en: All real PNRs belonging to the user from the database — used by the activation
        //     modal to display actual tickets instead of static test scenarios.
        $userTickets = $user->pnrs()
            ->orderByRaw("status = 'active' desc")
            ->latest()
            ->get(['pnr_code', 'last_name', 'status']);

        $airlineFromFlightNumber = static function (?string $flightNumber): string {
            $prefix = strtoupper(substr((string) $flightNumber, 0, 2));

            return match ($prefix) {
                'GA' => 'Garuda Indonesia',
                'SQ' => 'Singapore Airlines',
                'QZ' => 'Citilink',
                'JT' => 'Lion Air',
                'ID' => 'Batik Air',
                default => 'Rebound Air',
            };
        };

        $airportName = static function (?string $code): array {
            return match (strtoupper((string) $code)) {
                'CGK' => ['Jakarta', 'Jakarta'],
                'SIN' => ['Singapura', 'Singapore'],
                'HND' => ['Tokyo', 'Tokyo'],
                'KUL' => ['Kuala Lumpur', 'Kuala Lumpur'],
                'DPS' => ['Denpasar', 'Denpasar'],
                'SUB' => ['Surabaya', 'Surabaya'],
                default => ['Unknown', 'Unknown'],
            };
        };

        $statusLabel = static function (?string $status): array {
            return match ($status) {
                'delayed' => ['delayed', 'Terlambat +4j 25m', 'Delayed +4h 25m'],
                'cancelled' => ['cancelled', 'Dibatalkan', 'Cancelled'],
                'flown', 'completed' => ['on-time', 'Selesai', 'Completed'],
                default => ['on-time', 'Tepat Waktu', 'On Time'],
            };
        };

        $buildAlternativeFlights = static function (?MockGdsBooking $booking) use ($airlineFromFlightNumber, $airportName): array {
            if (!$booking) {
                return [];
            }

            [$fromCityId, $fromCityEn] = $airportName($booking->from_code);
            [$toCityId, $toCityEn] = $airportName($booking->to_code);

            return [
                [
                    'flightNumber' => 'GA830',
                    'airline' => 'Garuda Indonesia',
                    'airlineCode' => 'GA',
                    'aircraft' => 'Boeing 737-800',
                    'gate' => '4A',
                    'fromCity' => $fromCityId,
                    'fromCityEn' => $fromCityEn,
                    'fromCode' => $booking->from_code,
                    'toCity' => $toCityId,
                    'toCityEn' => $toCityEn,
                    'toCode' => $booking->to_code,
                    'depTime' => '12:40',
                    'arrTime' => '15:25',
                    'duration' => '2j 45m',
                    'durationEn' => '2h 45m',
                    'seatsAvailable' => 12,
                    'departureCountdownId' => 'Berangkat 45 menit lagi',
                    'departureCountdownEn' => 'Departs in 45 min',
                    'isRecommended' => true,
                ],
                [
                    'flightNumber' => 'SQ638',
                    'airline' => 'Singapore Airlines',
                    'airlineCode' => 'SQ',
                    'aircraft' => 'Airbus A350-900',
                    'gate' => '2A',
                    'fromCity' => $fromCityId,
                    'fromCityEn' => $fromCityEn,
                    'fromCode' => $booking->from_code,
                    'toCity' => $toCityId,
                    'toCityEn' => $toCityEn,
                    'toCode' => $booking->to_code,
                    'depTime' => '14:15',
                    'arrTime' => '17:05',
                    'duration' => '2j 50m',
                    'durationEn' => '2h 50m',
                    'seatsAvailable' => 8,
                    'departureCountdownId' => 'Berangkat 2 jam lagi',
                    'departureCountdownEn' => 'Departs in 2 hours',
                    'isRecommended' => false,
                ],
                [
                    'flightNumber' => 'QG524',
                    'airline' => 'Citilink (Garuda Group)',
                    'airlineCode' => 'QG',
                    'aircraft' => 'Airbus A320neo',
                    'gate' => '5B',
                    'fromCity' => $fromCityId,
                    'fromCityEn' => $fromCityEn,
                    'fromCode' => $booking->from_code,
                    'toCity' => $toCityId,
                    'toCityEn' => $toCityEn,
                    'toCode' => $booking->to_code,
                    'depTime' => '16:30',
                    'arrTime' => '19:15',
                    'duration' => '2j 45m',
                    'durationEn' => '2h 45m',
                    'seatsAvailable' => 15,
                    'departureCountdownId' => 'Berangkat sore ini',
                    'departureCountdownEn' => 'Departs this evening',
                    'isRecommended' => false,
                ],
                [
                    'flightNumber' => 'ID7153',
                    'airline' => 'Batik Air',
                    'airlineCode' => 'ID',
                    'aircraft' => 'Boeing 737-800',
                    'gate' => '1C',
                    'fromCity' => $fromCityId,
                    'fromCityEn' => $fromCityEn,
                    'fromCode' => $booking->from_code,
                    'toCity' => $toCityId,
                    'toCityEn' => $toCityEn,
                    'toCode' => $booking->to_code,
                    'depTime' => '18:00',
                    'arrTime' => '20:50',
                    'duration' => '2j 50m',
                    'durationEn' => '2h 50m',
                    'seatsAvailable' => 6,
                    'departureCountdownId' => 'Berangkat malam ini',
                    'departureCountdownEn' => 'Departs tonight',
                    'isRecommended' => false,
                ],
            ];
        };

        // id: Profil penerbangan per PNR — mencakup 'alternative' (penerbangan alternatif
        //     rekomendasi) agar kartu rekomendasi rebook di chat tidak merender "undefined"
        //     pada PNR nyata; bentuknya sama dengan objek flight.alternative di Alpine.
        // en: Per-PNR flight profile — includes 'alternative' (the recommended alternative
        //     flight) so the chat's rebook recommendation card never renders "undefined" for
        //     real PNRs; shape matches the Alpine flight.alternative object.
        $buildFlightProfile = static function (?MockGdsBooking $booking) use ($airlineFromFlightNumber, $airportName, $statusLabel, $buildAlternativeFlights): ?array {
            if (!$booking) {
                return null;
            }

            [$fromCityId, $fromCityEn] = $airportName($booking->from_code);
            [$toCityId, $toCityEn] = $airportName($booking->to_code);
            [$flightStatus, $statusId, $statusEn] = $statusLabel($booking->status);
            $departure = $booking->departure_time;
            $departureDateId = $departure?->format('d M Y, H.i') ?? '';
            $departureDateEn = $departure?->format('d M Y, H:i') ?? '';
            // id: arrTime di bawah = keberangkatan + 2j 45m, jadi durasi dihitung dari nilai yang sama
            //     agar kartu trip selalu konsisten dengan jam tiba yang ditampilkan.
            // en: arrTime below = departure + 2h 45m, so duration is derived from the same value
            //     to keep the trip card consistent with the displayed arrival time.
            $durationMinutes = 165;
            $durationId = intdiv($durationMinutes, 60) . 'j ' . ($durationMinutes % 60) . 'm';
            $durationEn = intdiv($durationMinutes, 60) . 'h ' . ($durationMinutes % 60) . 'm';

            return [
                'original' => [
                    'flightNumber' => $booking->flight_number,
                    'airline' => $airlineFromFlightNumber($booking->flight_number),
                    'airlineCode' => strtoupper(substr((string) $booking->flight_number, 0, 2)),
                    'fromCity' => $fromCityId,
                    'fromCityEn' => $fromCityEn,
                    'fromCode' => $booking->from_code,
                    'toCity' => $toCityId,
                    'toCityEn' => $toCityEn,
                    'toCode' => $booking->to_code,
                    'date' => $departure?->format('d M') ?? '',
                    'dateFullId' => $departureDateId,
                    'dateFullEn' => $departureDateEn,
                    'depTime' => $departure?->format('H:i') ?? '',
                    'arrTime' => $departure?->addHours(2)->addMinutes(45)?->format('H:i') ?? '',
                    'duration' => $durationId,
                    'durationEn' => $durationEn,
                    'aircraft' => $booking->aircraft ?? 'TBA',
                    'seat' => $booking->seat_number ?? '-',
                    'boardingGroup' => $booking->boarding_zone ?? '-',
                    'gate' => $booking->gate ?? '-',
                    'terminal' => $booking->terminal ?? '-',
                    'class' => $booking->cabin_class,
                    'statusId' => $statusId,
                    'statusEn' => $statusEn,
                    'delayTime' => $departure?->format('d M Y, H:i') ?? '',
                    'delayCauseId' => $booking->status === 'delayed' ? 'Cuaca buruk' : 'Normal',
                    'delayCauseEn' => $booking->status === 'delayed' ? 'Bad weather' : 'Normal',
                    'changeAllowedId' => $booking->status === 'delayed' ? 'Ya' : 'Ya',
                    'changeAllowedEn' => $booking->status === 'delayed' ? 'Yes' : 'Yes',
                    'feeAmountId' => $booking->status === 'delayed' ? 'Rp0' : 'Rp0',
                    'feeAmountEn' => $booking->status === 'delayed' ? '$0' : '$0',
                    'fareDiffId' => 'Berlaku',
                    'fareDiffEn' => 'Applies',
                ],
                'alternative' => $buildAlternativeFlights($booking)[0] ?? null,
                'flightStatus' => $flightStatus,
            ];
        };

        // id: Lapis 1 saran prompt kontekstual — dibangun dari data booking riil (status GDS + nomor
        //     penerbangan) tanpa panggilan AI, sehingga chip saran tampil instan untuk semua PNR.
        //     Logika teks identik dengan ChatController::ruleBasedSuggestions() agar endpoint saran
        //     AI menghasilkan fallback yang sama ketika Qwen offline / API key belum dikonfigurasi.
        // en: Layer-1 contextual prompt suggestions — built from real booking data (GDS status + flight
        //     number) with no AI call, so suggestion chips render instantly for every PNR. The wording is
        //     identical to ChatController::ruleBasedSuggestions() so the AI suggestion endpoint yields the
        //     same fallback when Qwen is offline / the API key is not configured.
        $buildSuggestions = static function (?MockGdsBooking $booking): array {
            if (!$booking) {
                return [];
            }

            $flight = trim((string) $booking->flight_number);

            return match ($booking->status) {
                'delayed' => [
                    ['id' => "Lihat opsi rebooking gratis untuk penerbangan {$flight}", 'en' => "View free rebooking options for flight {$flight}"],
                    ['id' => 'Cek hak kompensasi & meal voucher saya', 'en' => 'Check my compensation & meal voucher entitlements'],
                ],
                'cancelled' => [
                    ['id' => "Klaim kompensasi pembatalan penerbangan {$flight}", 'en' => "Claim cancellation compensation for flight {$flight}"],
                    ['id' => 'Lihat opsi rebooking & refund tersedia', 'en' => 'View available rebooking & refund options'],
                ],
                default => [
                    ['id' => "Cek status terkini penerbangan {$flight}", 'en' => "Check the latest status of flight {$flight}"],
                    ['id' => 'Berapa batas bagasi terdaftar tiket saya?', 'en' => 'What is my checked baggage allowance?'],
                ],
            };
        };

        $bookingsByPnr = MockGdsBooking::whereIn(
            'pnr_code',
            $userTickets->pluck('pnr_code')->filter()->values()->all()
        )->get()->keyBy('pnr_code');

        $activeFlightProfile = $buildFlightProfile($activePnrCode ? ($bookingsByPnr[$activePnrCode] ?? null) : null);
        $flightProfiles = $bookingsByPnr->mapWithKeys(function (MockGdsBooking $booking) use ($buildFlightProfile) {
            return [$booking->pnr_code => $buildFlightProfile($booking)];
        })->all();
        $alternativeFlightsByPnr = $bookingsByPnr->mapWithKeys(function (MockGdsBooking $booking) use ($buildAlternativeFlights) {
            return [$booking->pnr_code => $buildAlternativeFlights($booking)];
        })->all();
        $suggestionsByPnr = $bookingsByPnr->mapWithKeys(function (MockGdsBooking $booking) use ($buildSuggestions) {
            return [$booking->pnr_code => $buildSuggestions($booking)];
        })->all();


        // id: Sesi chat AI Agent milik pengguna dari database (agent_chat_sessions + chat_messages + mock_gds_bookings)
        // en: User's AI Agent chat sessions from database (agent_chat_sessions + chat_messages + mock_gds_bookings)
        $chatSessions = AgentChatSession::where('user_id', $user->id)
            ->with([
                'messages' => function ($query) {
                    $query->latest('sent_at');
                }
            ])
            ->latest('updated_at')
            ->get()
            ->map(function ($session) {
                $gdsBooking = MockGdsBooking::where('pnr_code', $session->pnr_code)->first();
                $latestMsg = $session->messages->first();

                return [
                    'id' => $session->id,
                    'pnr_code' => $session->pnr_code,
                    'context_summary' => $session->context_summary,
                    'last_message' => $latestMsg?->message_content ?? 'Belum ada pesan.',
                    'last_message_sender' => $latestMsg?->sender ?? 'system',
                    'last_message_time' => $latestMsg?->sent_at ? $latestMsg->sent_at->format('H:i') : $session->updated_at->format('H:i'),
                    'flight_number' => $gdsBooking?->flight_number ?? $session->pnr_code,
                    'from_code' => $gdsBooking?->from_code ?? 'CGK',
                    'to_code' => $gdsBooking?->to_code ?? 'SIN',
                    'departure_time' => $gdsBooking?->departure_time?->format('d M Y') ?? '',
                    'status' => $gdsBooking?->status ?? 'on_time',
                    'cabin_class' => $gdsBooking?->cabin_class ?? 'Economy',
                ];
            });

        // id: Notifikasi operasional milik user dari tabel notifications — dirender dropdown navbar,
        //     menggantikan tiga kartu alert statis yang dulu di-hardcode.
        // en: The user's operational notifications from the notifications table — rendered in the navbar
        //     dropdown, replacing the three static alert cards previously hardcoded.
        $notifications = $user->appNotifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($notification) => [
                'id' => $notification->id,
                'pnr_code' => $notification->pnr_code,
                'type' => $notification->type,
                'title_id' => $notification->title_id,
                'title_en' => $notification->title_en,
                'message_id' => $notification->message_id,
                'message_en' => $notification->message_en,
                'is_read' => (bool) $notification->is_read,
                'created_at' => $notification->created_at->toIso8601String(),
            ]);

        // id: Katalog terjemahan dinamis — baris tabel translations menimpa nilai file lang statis
        //     (lang/id/messages.php & lang/en/messages.php), sehingga teks UI dapat diubah lewat
        //     database/API tanpa deploy ulang; key tanpa baris DB tetap memakai nilai file.
        // en: Dynamic translation catalogue — translations table rows override the static lang file
        //     values (lang/id/messages.php & lang/en/messages.php), so UI copy can be edited via the
        //     database/API without redeploying; keys without a DB row keep the file value.
        $translations = Translation::catalogue();

        // id: Hasil rebooking user dari tabel rebookings — menggantikan state localStorage frontend.
        //     Dipetakan menjadi rebookingsByPnr (PNR -> objek penerbangan alternatif) agar status
        //     'rebooked' + data alternatif pulih dari database saat halaman dimuat, lintas perangkat.
        // en: The user's rebooking results from the rebookings table — replacing the frontend
        //     localStorage state. Mapped to rebookingsByPnr (PNR -> alternative flight object) so the
        //     'rebooked' status + alternative data are restored from the database on page load, across devices.
        $rebookingsByPnr = Rebooking::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(fn(Rebooking $rebooking) => [$rebooking->pnr_code => $rebooking->alternative_flight])
            ->all();

        return view('welcome', [
            'hasSetupPnr' => $activePnrCode !== null,
            'activePnrCode' => $activePnrCode,
            'activeFlightProfile' => $activeFlightProfile,
            'flightProfiles' => $flightProfiles,
            'alternativeFlightsByPnr' => $alternativeFlightsByPnr,
            'suggestionsByPnr' => $suggestionsByPnr,
            'userTickets' => $userTickets,
            'chatSessions' => $chatSessions,
            'notifications' => $notifications,
            'translations' => $translations,
            'rebookingsByPnr' => $rebookingsByPnr,
        ]);

    })->name('dashboard');

    Route::delete('/api/chat/session/{id}', [\App\Http\Controllers\ChatController::class, 'deleteSession']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


