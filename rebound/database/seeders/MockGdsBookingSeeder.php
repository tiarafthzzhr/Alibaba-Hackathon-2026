<?php

namespace Database\Seeders;

use App\Models\MockGdsBooking;
use Illuminate\Database\Seeder;

// id: Seeder untuk mock_gds_bookings — mengisi "GDS tiruan" dengan booking demo
//     agar verifikasi PNR di modal punya data valid untuk dicocokkan.
// en: Seeder for mock_gds_bookings — populates the mock GDS with demo bookings
//     so the modal's PNR verification has valid records to match against.
class MockGdsBookingSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = [
            // id: Tiket demo utama yang dipakai di sidebar & simulasi scan modal
            // en: Main demo tickets used by the sidebar & the modal's scan simulation
            [
                'pnr_code' => 'GA826',
                'last_name' => 'ZAKARIA',
                'flight_number' => 'GA 826',
                'from_code' => 'CGK',
                'to_code' => 'SIN',
                'departure_time' => '2026-09-28 08:25:00',
                'cabin_class' => 'Economy',
                'status' => 'delayed', // id: Sengaja delayed untuk demo krisis
            ],
            [
                'pnr_code' => 'SQ951',
                'last_name' => 'MAULANA',
                'flight_number' => 'SQ 951',
                'from_code' => 'CGK',
                'to_code' => 'SIN',
                'departure_time' => '2026-09-27 14:10:00',
                'cabin_class' => 'Business',
                'status' => 'delayed',
            ],
            [
                'pnr_code' => 'SQ638',
                'last_name' => 'ISTIQOMAH',
                'flight_number' => 'SQ 638',
                'from_code' => 'CGK',
                'to_code' => 'KUL',
                'departure_time' => '2026-09-29 06:40:00',
                'cabin_class' => 'Economy',
                'status' => 'on_time',
            ],
            // id: PNR milik akun demo haikal.firmansyah@rebound.ai
            // en: PNR belonging to the haikal.firmansyah@rebound.ai demo account
            [
                'pnr_code' => 'GA826K',
                'last_name' => 'FIRMANSYAH',
                'flight_number' => 'GA 826',
                'from_code' => 'CGK',
                'to_code' => 'SIN',
                'departure_time' => '2026-09-20 08:25:00',
                'cabin_class' => 'Economy',
                'status' => 'delayed',
            ],
            // id: Tiket tambahan yang tampil di sidebar
            // en: Extra tickets shown in the sidebar
            [
                'pnr_code' => 'QZ502',
                'last_name' => 'AZZAHRA',
                'flight_number' => 'QZ 502',
                'from_code' => 'CGK',
                'to_code' => 'KUL',
                'departure_time' => '2026-09-01 11:15:00',
                'cabin_class' => 'Economy',
                'status' => 'delayed',
            ],
            [
                'pnr_code' => 'JT028',
                'last_name' => 'ZAKARIA',
                'flight_number' => 'JT 028',
                'from_code' => 'CGK',
                'to_code' => 'DPS',
                'departure_time' => '2026-09-03 09:50:00',
                'cabin_class' => 'Economy',
                'status' => 'cancelled',
            ],
        ];

        foreach ($bookings as $booking) {
            MockGdsBooking::updateOrCreate(
                ['pnr_code' => $booking['pnr_code'], 'last_name' => $booking['last_name']],
                $booking
            );
        }
    }
}
