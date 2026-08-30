<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// id: Detail trip (pesawat, kursi, zona boarding, gate, terminal) diambil dari GDS mock
//     agar kartu trip menampilkan data nyata — bukan placeholder frontend.
// en: Trip details (aircraft, seat, boarding zone, gate, terminal) come from the mock GDS
//     so the trip card renders real data instead of frontend placeholders.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mock_gds_bookings', function (Blueprint $table) {
            $table->string('aircraft')->nullable()->after('cabin_class');
            $table->string('seat_number')->nullable()->after('aircraft');
            $table->string('boarding_zone')->nullable()->after('seat_number');
            $table->string('gate')->nullable()->after('boarding_zone');
            $table->string('terminal')->nullable()->after('gate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mock_gds_bookings', function (Blueprint $table) {
            $table->dropColumn(['aircraft', 'seat_number', 'boarding_zone', 'gate', 'terminal']);
        });
    }
};
