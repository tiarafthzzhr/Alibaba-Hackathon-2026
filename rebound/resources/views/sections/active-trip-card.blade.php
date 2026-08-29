{{-- #BACKEND Kartu Status Perjalanan Aktif / Active Trip Status Card
     id: Menampilkan kartu ringkasan status penerbangan aktif (rute, nomor penerbangan, badge status, waktu delay/tepat waktu, dan penyebab keterlambatan).
         Di backend: data diambil real-time dari GDS webhook / flight tracking API + tabel `bookings` & `flights`.
     en: Displays active flight status summary card (route, flight number, status badge, delayed/on-time status, and delay reasons).
         In backend: data fetched real-time from GDS webhook / flight tracking API + `bookings` & `flights` tables. --}}
<!-- Active Trip Status Card (Compact & Balanced, Figma Nodes 3:142, 14:693, 14:729, 14:656) -->
<div class="w-full max-w-[620px] mx-auto mb-2 bg-white rounded-lg border border-slate-200 p-2.5 sm:p-3 shadow-2xs transition hover:border-slate-300 select-none">
    
    {{-- id: Baris Atas: Rute Penerbangan & Badge Status Dinamis
         en: Top Row: Flight Route & Dynamic Status Badge --}}
    <!-- Top Row: Flight Route & Status Badge -->
    <div class="flex items-center justify-between gap-2.5">
        <!-- Route & Airline Icon -->
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 transition text-[10px]"
                 :class="flightStatus === 'delayed' ? 'bg-amber-100 text-amber-700' : (flightStatus === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-brand-600')">
                <i class="fa-solid text-[10px]"
                   :class="flightStatus === 'delayed' ? 'fa-plane-slash' : (flightStatus === 'cancelled' ? 'fa-ban' : 'fa-plane')"></i>
            </div>
            
            <div class="flex items-center gap-1.5">
                <span class="text-xs sm:text-sm font-bold text-slate-900 tracking-tight"
                      x-text="activeFlight.fromCode"></span>
                <i class="fa-solid fa-arrow-right-long text-[8px] text-slate-400"></i>
                <span class="text-xs sm:text-sm font-bold text-slate-900 tracking-tight"
                      x-text="activeFlight.toCode"></span>
            </div>
        </div>

        {{-- id: Badge Status Dinamis (Tepat Waktu / Terlambat / Dibatalkan / Telah Dialihkan)
             en: Dynamic Status Badge (On Time / Delayed / Cancelled / Rebooked) --}}
        <!-- Status Badge Dynamic -->
        <div>
            <!-- On Time Badge -->
            <template x-if="flightStatus === 'on-time' || flightStatus === 'on_time' || flightStatus === 'active'">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 border border-emerald-200 rounded text-[9.5px] font-bold text-emerald-700">
                    <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                    <span x-text="lang === 'id' ? 'Tepat Waktu' : 'On Time'"></span>
                </span>
            </template>

            <!-- Delayed Badge (Nodes 14:693, 14:729) -->
            <template x-if="flightStatus === 'delayed'">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-amber-50 border border-amber-200 rounded text-[9.5px] font-bold text-amber-700">
                    <i class="fa-regular fa-clock text-[8.5px]"></i>
                    <span x-text="lang === 'id' ? (flight.original.statusId || 'Terlambat +4j 25m') : (flight.original.statusEn || 'Delayed +4h 25m')"></span>
                </span>
            </template>

            <!-- Cancelled Badge -->
            <template x-if="flightStatus === 'cancelled'">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-rose-50 border border-rose-200 rounded text-[9.5px] font-bold text-rose-700">
                    <i class="fa-solid fa-ban text-[8.5px]"></i>
                    <span x-text="lang === 'id' ? 'Dibatalkan' : 'Cancelled'"></span>
                </span>
            </template>

            <!-- Rebooked / Selesai Badge -->
            <template x-if="flightStatus === 'rebooked'">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 border border-blue-200 rounded text-[9.5px] font-bold text-brand-700">
                    <i class="fa-solid fa-check text-[8.5px]"></i>
                    <span x-text="lang === 'id' ? 'Terjadwal Ulang' : 'Rescheduled'"></span>
                </span>
            </template>
        </div>
    </div>

    {{-- id: Baris Bawah: Info Maskapai & Estimasi Waktu Berangkat
         en: Bottom Row: Airline Info & Estimated Departure Timing --}}
    <!-- Bottom Row: Airline Info & Timing -->
    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
        <!-- Airline & Flight No -->
        <div class="flex items-center gap-2">
            <span class="font-medium text-slate-800 text-[10.5px]"
                  x-text="flightStatus === 'rebooked' ? (flight.alternative.flightNumber + ' • ' + flight.alternative.airline) : ((flight.original.flightNumber || selectedTicketId) + ' • ' + (flight.original.airline || '-'))"></span>
        </div>

        <!-- Schedule / Delay details -->
        <div class="text-right">
            <!-- Normal Time -->
            <template x-if="flightStatus === 'on-time' || flightStatus === 'on_time' || flightStatus === 'active'">
                {{-- id: Fallback tanggal berantai dari data backend (dateFullId/date), bukan placeholder Figma '30 Nov'.
                     en: Date fallback chain from backend data (dateFullId/date), not the '30 Nov' Figma placeholder. --}}
                <span class="font-semibold text-slate-800 text-[11px]"
                      x-text="lang === 'id' ? (flight.original.dateFullId || flight.original.date || '-') : (flight.original.dateFullEn || flight.original.date || '-')"></span>
            </template>

            <!-- Delayed Time & Reason (Node 14:729, 14:656) -->
            <template x-if="flightStatus === 'delayed'">
                <div>
                    <div class="font-bold text-slate-900 text-[11px]" x-text="flight.original.delayTime || flight.original.dateFullId || '-'"></div>
                    <div class="text-[10px] text-amber-600 font-medium flex items-center justify-end gap-1 mt-0.5">
                        <i class="fa-solid fa-cloud-bolt text-[9px]"></i>
                        <span x-text="lang === 'id' ? 'Penyebab: ' + (flight.original.delayCauseId || 'Cuaca buruk') : 'Cause: ' + (flight.original.delayCauseEn || 'Bad weather')"></span>
                    </div>
                </div>
            </template>

            <!-- Cancelled Time & Reason -->
            <template x-if="flightStatus === 'cancelled'">
                <div>
                    <span class="font-bold text-rose-700 text-[11px]" x-text="lang === 'id' ? 'Penerbangan Dibatalkan' : 'Flight Cancelled'"></span>
                </div>
            </template>

            <!-- Rebooked Time -->
            <template x-if="flightStatus === 'rebooked'">
                <div>
                    <span class="font-bold text-emerald-700 text-[11px]" x-text="activeFlight.date + ', ' + activeFlight.depTime"></span>
                    <span class="text-[10px] text-emerald-600 block" x-text="lang === 'id' ? 'Siap Berangkat' : 'Ready for Boarding'"></span>
                </div>
            </template>
        </div>
    </div>
</div>
