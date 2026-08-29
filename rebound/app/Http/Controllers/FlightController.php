<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgentChatSession;
use App\Models\MockGdsBooking;
use App\Models\Notification;
use App\Models\Rebooking;
use App\Models\UserPnr;
use App\Services\AtlasSandboxFlightSearch;
use Carbon\Carbon;

class FlightController extends Controller
{
    /**
     * Returns alternatives from Atlas ATRIP Sandbox when configured. The local
     * demo inventory remains a safe fallback for unsupported sandbox routes.
     */
    public function alternatives(Request $request, AtlasSandboxFlightSearch $atlas): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'from' => 'nullable|string|size:3|alpha',
            'to' => 'nullable|string|size:3|alpha',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $from = strtoupper($request->query('from', 'CGK'));
        $to = strtoupper($request->query('to', 'SIN'));
        $date = Carbon::parse($request->query('date', now()->addDay()->toDateString()));
        $atlasResult = $atlas->search($from, $to, $date);
        $flights = $atlasResult['available'] ? $atlasResult['flights'] : $this->demoAlternatives($from, $to);

        return response()->json([
            'status' => 'success',
            'route' => "{$from} -> {$to}",
            'date' => $date->toDateString(),
            'source' => $atlasResult['available'] ? 'atlas_sandbox' : 'rebound_demo_fallback',
            'is_sandbox' => true,
            'count' => count($flights),
            'data' => $flights,
        ]);
    }

    public function lookup(Request $request)
    {
        // 1. Validasi Input Frontend
        $request->validate([
            'pnr_code' => 'required|string|size:6|alpha_num',
            'last_name' => 'required|string',
        ]);

        $pnrCode = strtoupper($request->pnr_code);
        $lastName = $request->last_name;
        $user = $request->user(); // Mendapatkan data user yang sedang login

        // 2. id: Cek PNR ke Mock GDS (tabel mock_gds_bookings) — Atlas CLI tidak punya perintah lookup PNR
        // en: Check the PNR against the Mock GDS (mock_gds_bookings table) — the Atlas CLI has no PNR lookup command
        $booking = MockGdsBooking::where('pnr_code', $pnrCode)->first();

        // 3. Tangani jika PNR tidak ditemukan atau nama penumpang tidak cocok
        if (! $booking || ! $this->matchesLastName($booking, $lastName)) {
            return response()->json([
                'status' => 'error',
                'message' => 'PNR tidak ditemukan atau nama penumpang tidak sesuai.'
            ], 404);
        }

        // 4. Susun data booking dari Mock GDS menjadi payload untuk frontend
        $flightData = $this->bookingPayload($booking);

        // 5. Otorisasi: Simpan atau perbarui data kepemilikan PNR di database lokal Rebound
        UserPnr::updateOrCreate(
            [
                'user_id' => $user->id, 
                'pnr_code' => $pnrCode
            ],
            [
                'last_name' => $lastName, 
                'status' => 'active'
            ]
        );

        // 6. Kembalikan data mentah Atlas ke Frontend
        return response()->json([
            'status' => 'success',
            'message' => 'Otorisasi PNR berhasil.',
            'data' => $flightData
        ], 200);
    }

    /**
     * id: Mengaktifkan PNR hasil verifikasi modal aktivasi tiket dan menyimpannya ke database,
     *     sehingga status hasSetupPnr bertahan setelah halaman di-refresh.
     * en: Activates the PNR verified by the ticket activation modal and persists it to the database,
     *     so the hasSetupPnr state survives a page refresh.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'pnr_code' => 'required|string',
            'last_name' => 'nullable|string|max:100',
        ]);

        // id: Normalisasi kode PNR (huruf besar, tanpa tanda hubung/spasi) agar muat di kolom varchar(6)
        // en: Normalize PNR code (uppercase, no dashes/spaces) so it fits the varchar(6) column
        $pnrCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('pnr_code')));

        if (strlen($pnrCode) < 5 || strlen($pnrCode) > 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format kode PNR tidak valid.',
            ], 422);
        }

        $user = $request->user();

        // id: Hanya boleh ada satu PNR aktif per user — nonaktifkan PNR aktif sebelumnya
        // en: Only one active PNR per user — deactivate any previously active PNR
        UserPnr::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('pnr_code', '!=', $pnrCode)
            ->update(['status' => 'changed']);

        $pnr = UserPnr::updateOrCreate(
            ['user_id' => $user->id, 'pnr_code' => $pnrCode],
            [
                'last_name' => $request->input('last_name') ?: $user->name,
                'status' => 'active',
            ]
        );

        // id: Tiket yang diaktifkan juga langsung punya sesi chat agar tampil di sidebar kiri
        // en: The activated ticket also gets a chat session right away so it shows in the left sidebar
        $session = $this->ensureChatSession($user->id, $pnrCode);
        $booking = MockGdsBooking::where('pnr_code', $pnrCode)->first();

        // id: Jika GDS menyatakan penerbangan terganggu, langsung kirim alert ke pusat notifikasi user
        // en: When the GDS reports a disrupted flight, immediately push an alert to the user's notification center
        $this->ensureDisruptionNotification($user->id, $booking, $pnrCode);

        return response()->json([
            'status' => 'success',
            'message' => 'PNR berhasil diaktifkan.',
            'data' => [
                'pnr_code' => $pnr->pnr_code,
                // id: Kartu sesi untuk sidebar kiri — bentuknya sama dengan respons verify(),
                //     sehingga frontend bisa langsung menampilkannya tanpa refresh halaman.
                // en: Session card for the left sidebar — same shape as verify()'s response,
                //     so the frontend can display it immediately without a page refresh.
                'session' => [
                    'id' => $session->id,
                    'pnr_code' => $session->pnr_code,
                    'context_summary' => $session->context_summary,
                    'last_message' => 'Belum ada pesan.',
                    'last_message_sender' => 'system',
                    'last_message_time' => null,
                    'flight_number' => $booking?->flight_number,
                    'from_code' => $booking?->from_code,
                    'to_code' => $booking?->to_code,
                    'departure_time' => $booking?->departure_time?->format('d M Y') ?? '',
                    'status' => $booking?->status ?? 'active',
                    'cabin_class' => $booking?->cabin_class,
                ],
            ],
        ], 200);
    }

    /**
     * id: Simpan hasil rebooking user — menggantikan state localStorage frontend dengan baris tabel
     *     rebookings, satu baris per user + PNR (updateOrCreate). Objek penerbangan alternatif yang
     *     dipilih disimpan utuh sebagai JSON sehingga status rebooked pulih dari database, bukan lokal.
     * en: Persist the user's rebooking result — replaces the frontend localStorage state with a row in
     *     the rebookings table, one row per user + PNR (updateOrCreate). The chosen alternative flight
     *     object is stored whole as JSON so the rebooked state is restored from the database, not locally.
     */
    public function rebook(Request $request)
    {
        $request->validate([
            'pnr' => 'required|string|max:10',
            'alternative' => 'required|array',
            'alternative.flightNumber' => 'required|string|max:12',
        ]);

        $user = $request->user();
        // id: Normalisasi kode PNR (huruf besar, tanpa tanda hubung/spasi) — pola sama dengan verify()
        // en: Normalize the PNR code (uppercase, no dashes/spaces) — same pattern as verify()
        $pnrCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('pnr')));

        $rebooking = Rebooking::updateOrCreate(
            ['user_id' => $user->id, 'pnr_code' => $pnrCode],
            ['alternative_flight' => $request->input('alternative')]
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'pnr_code' => $rebooking->pnr_code,
                'alternative_flight' => $rebooking->alternative_flight,
                'rebooked_at' => $rebooking->updated_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * id: Verifikasi PNR ke GDS: user mengisi Kode Booking (PNR) + Nama Penumpang di modal,
     *     Laravel mengecek ke GDS (Mock GDS: tabel mock_gds_bookings di MySQL lokal). Jika GDS
     *     menjawab valid, kode PNR dan ID user yang sedang login dicatat ke tabel user_pnrs.
     * en: GDS PNR verification: the user enters the Booking Code (PNR) + Passenger Name in the modal,
     *     Laravel checks the GDS (Mock GDS: mock_gds_bookings table in local MySQL). When the GDS
     *     answers valid, the PNR code and the logged-in user ID are recorded into the user_pnrs table.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pnr' => 'required|string',
            'passenger' => 'required|string|max:100',
        ]);

        // id: Normalisasi kode PNR (huruf besar, tanpa tanda hubung/spasi)
        // en: Normalize the PNR code (uppercase, no dashes/spaces)
        $pnrCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('pnr')));
        $passenger = trim($request->input('passenger'));

        if (strlen($pnrCode) < 5 || strlen($pnrCode) > 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format kode PNR tidak valid (harus 5-6 karakter alfanumerik).',
            ], 422);
        }

        $user = $request->user();

        // id: Tanyakan PNR + nama penumpang ke GDS (tabel mock_gds_bookings)
        // en: Query the PNR + passenger name to the GDS (mock_gds_bookings table)
        $booking = MockGdsBooking::where('pnr_code', $pnrCode)->first();

        // id: GDS menjawab TIDAK VALID — PNR tidak ditemukan atau nama penumpang tidak cocok
        // en: The GDS answered INVALID — the PNR was not found or the passenger name does not match
        if (! $booking || ! $this->matchesLastName($booking, $passenger)) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'PNR tidak ditemukan atau nama penumpang tidak sesuai. Cek ulang kode PNR dan nama penumpang Anda.',
            ], 404);
        }

        // id: GDS menjawab VALID — catat kode PNR + ID pengguna yang login ke database lokal MySQL (tabel user_pnrs)
        // en: The GDS answered VALID — record the PNR code + logged-in user ID into the local MySQL database (user_pnrs table)
        UserPnr::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('pnr_code', '!=', $pnrCode)
            ->update(['status' => 'changed']);

        UserPnr::updateOrCreate(
            ['user_id' => $user->id, 'pnr_code' => $pnrCode],
            ['last_name' => $passenger, 'status' => 'active']
        );

        // id: Tiket baru (Add Ticket PNR) langsung mendapat sesi chat AI Agent sehingga
        //     muncul di sidebar kiri tanpa perlu menunggu pesan chat pertama.
        // en: A newly added ticket (Add Ticket PNR) immediately gets an AI Agent chat session
        //     so it appears in the left sidebar without waiting for the first chat message.
        $session = $this->ensureChatSession($user->id, $pnrCode, $booking);

        // id: Jika GDS menyatakan penerbangan terganggu, langsung kirim alert ke pusat notifikasi user
        // en: When the GDS reports a disrupted flight, immediately push an alert to the user's notification center
        $this->ensureDisruptionNotification($user->id, $booking, $pnrCode);

        return response()->json([
            'status' => 'success',
            'message' => 'PNR valid menurut GDS dan telah dicatat ke akun Anda.',
            'data' => [
                'pnr_code' => $pnrCode,
                'flight' => $this->bookingPayload($booking),
                // id: Kartu sesi untuk sidebar kiri — bentuknya sama dengan hasil mapping route dashboard
                // en: Session card for the left sidebar — same shape as the dashboard route mapping
                'session' => [
                    'id' => $session->id,
                    'pnr_code' => $session->pnr_code,
                    'context_summary' => $session->context_summary,
                    'last_message' => 'Belum ada pesan.',
                    'last_message_sender' => 'system',
                    'last_message_time' => null,
                    'flight_number' => $booking->flight_number,
                    'from_code' => $booking->from_code,
                    'to_code' => $booking->to_code,
                    'departure_time' => $booking->departure_time?->format('d M Y') ?? '',
                    'status' => $booking->status,
                    'cabin_class' => $booking->cabin_class,
                ],
            ],
        ], 200);
    }

    // id: Bandingkan nama penumpang yang diinput dengan nama di Mock GDS
    //     (case-insensitive; boleh lebih panjang, mis. "Zakaria MP" cocok dengan "ZAKARIA")
    // en: Compare the entered passenger name with the Mock GDS name
    //     (case-insensitive; extra suffix allowed, e.g. "Zakaria MP" matches "ZAKARIA")
    private function matchesLastName(MockGdsBooking $booking, string $passenger): bool
    {
        $stored = strtoupper($booking->last_name);
        $input = strtoupper($passenger);

        return $input === $stored || str_starts_with($input, $stored);
    }

    // id: Pastikan user punya satu sesi chat AI Agent per PNR (dipakai sidebar kiri).
    //     Sesi yang sudah ada tidak ditimpa — riwayat chat tetap utuh.
    // en: Ensure the user has exactly one AI Agent chat session per PNR (feeds the left sidebar).
    //     Existing sessions are left untouched so chat history stays intact.
    private function ensureChatSession(int $userId, string $pnrCode, ?MockGdsBooking $booking = null): AgentChatSession
    {
        $booking ??= MockGdsBooking::where('pnr_code', $pnrCode)->first();

        $summary = $booking
            ? 'Sesi obrolan penerbangan ' . $booking->flight_number . ' rute '
                . $booking->from_code . ' ➔ ' . $booking->to_code . ' untuk PNR ' . $pnrCode
            : 'Sesi obrolan penerbangan untuk PNR ' . $pnrCode;

        return AgentChatSession::firstOrCreate(
            ['user_id' => $userId, 'pnr_code' => $pnrCode],
            ['context_summary' => $summary]
        );
    }

    // id: Buat notifikasi gangguan (delay/cancelled) saat tiket diverifikasi/diaktifkan dan GDS
    //     menyatakan penerbangannya terganggu. firstOrCreate per user+pnr+type mencegah duplikat
    //     saat user memverifikasi ulang tiket yang sama.
    // en: Create a disruption notification (delay/cancelled) when a ticket is verified/activated
    //     and the GDS reports its flight as disrupted. firstOrCreate per user+pnr+type prevents
    //     duplicates when the user re-verifies the same ticket.
    private function ensureDisruptionNotification(int $userId, ?MockGdsBooking $booking, string $pnrCode): void
    {
        if (! $booking || ! in_array($booking->status, ['delayed', 'cancelled'], true)) {
            return;
        }

        $isDelayed = $booking->status === 'delayed';
        $flight = $booking->flight_number;

        Notification::firstOrCreate(
            ['user_id' => $userId, 'pnr_code' => $pnrCode, 'type' => $isDelayed ? 'delay' : 'cancelled'],
            [
                'title_id' => $isDelayed ? 'Keterlambatan Penerbangan' : 'Penerbangan Dibatalkan',
                'title_en' => $isDelayed ? 'Flight Delay' : 'Flight Cancelled',
                'message_id' => $isDelayed
                    ? "Penerbangan {$flight} Anda resmi ditunda oleh pihak maskapai. Opsi rebooking telah aktif."
                    : "Penerbangan {$flight} Anda dibatalkan oleh pihak maskapai. Opsi rebooking & kompensasi telah aktif.",
                'message_en' => $isDelayed
                    ? "Your flight {$flight} has been officially delayed by the airline. Rebooking options are now active."
                    : "Your flight {$flight} has been cancelled by the airline. Rebooking & compensation options are now active.",
            ]
        );
    }

    // id: Ubah booking Mock GDS menjadi payload penerbangan untuk frontend
    // en: Turn a Mock GDS booking into the flight payload for the frontend
    private function bookingPayload(MockGdsBooking $booking): array
    {
        return [
            'pnr' => $booking->pnr_code,
            'last_name' => $booking->last_name,
            'flight_number' => $booking->flight_number,
            'from' => $booking->from_code,
            'to' => $booking->to_code,
            'departure_time' => $booking->departure_time?->toIso8601String(),
            'cabin_class' => $booking->cabin_class,
            'status' => $booking->status,
        ];
    }

    private function demoAlternatives(string $from, string $to): array
    {
        return [
            [
                'flightNumber' => 'GA830', 'airline' => 'Garuda Indonesia', 'airlineCode' => 'GA',
                'aircraft' => 'Boeing 737-800', 'gate' => '4A', 'fromCode' => $from, 'toCode' => $to,
                'depTime' => '12:40', 'arrTime' => '15:25', 'duration' => '2j 45m', 'seatsAvailable' => 12,
                'waiverStatus' => 'Eligible (Waiver 72A)', 'feeAmount' => 0, 'isRecommended' => true,
                'source' => 'rebound_demo_fallback',
            ],
            [
                'flightNumber' => 'SQ638', 'airline' => 'Singapore Airlines', 'airlineCode' => 'SQ',
                'aircraft' => 'Airbus A350-900', 'gate' => '2A', 'fromCode' => $from, 'toCode' => $to,
                'depTime' => '14:15', 'arrTime' => '17:05', 'duration' => '2j 50m', 'seatsAvailable' => 8,
                'waiverStatus' => 'Eligible (Waiver 72A)', 'feeAmount' => 0, 'isRecommended' => false,
                'source' => 'rebound_demo_fallback',
            ],
            [
                'flightNumber' => 'QG524', 'airline' => 'Citilink (Garuda Group)', 'airlineCode' => 'QG',
                'aircraft' => 'Airbus A320neo', 'gate' => '5B', 'fromCode' => $from, 'toCode' => $to,
                'depTime' => '16:30', 'arrTime' => '19:15', 'duration' => '2j 45m', 'seatsAvailable' => 15,
                'waiverStatus' => 'Eligible (Waiver 72A)', 'feeAmount' => 0, 'isRecommended' => false,
                'source' => 'rebound_demo_fallback',
            ],
            [
                'flightNumber' => 'ID7153', 'airline' => 'Batik Air', 'airlineCode' => 'ID',
                'aircraft' => 'Boeing 737-800', 'gate' => '1C', 'fromCode' => $from, 'toCode' => $to,
                'depTime' => '18:00', 'arrTime' => '20:50', 'duration' => '2j 50m', 'seatsAvailable' => 6,
                'waiverStatus' => 'Eligible (Waiver 72A)', 'feeAmount' => 0, 'isRecommended' => false,
                'source' => 'rebound_demo_fallback',
            ],
        ];
    }
}
