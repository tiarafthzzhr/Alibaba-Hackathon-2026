<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// id: Model untuk tabel mock_gds_bookings — tiruan GDS maskapai di database lokal.
//     Tabel ini berperan sebagai "sumber kebenaran" PNR pengganti GDS sungguhan (Amadeus/Sabre),
//     karena Atlas CLI saat ini belum menyediakan perintah lookup PNR.
// en: Model for the mock_gds_bookings table — a mock airline GDS living in the local database.
//     This table acts as the PNR source of truth in place of a real GDS (Amadeus/Sabre),
//     because the Atlas CLI does not currently provide a PNR lookup command.
class MockGdsBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'pnr_code',
        'last_name',
        'flight_number',
        'from_code',
        'to_code',
        'departure_time',
        'cabin_class',
        'aircraft',
        'seat_number',
        'boarding_zone',
        'gate',
        'terminal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
        ];
    }
}
