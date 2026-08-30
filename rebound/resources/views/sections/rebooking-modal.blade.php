{{-- #BACKEND Modal Animasi Telemetri Rebooking / Instant Aviation Rebooking Step Animation Modal
     id: Menampilkan modal animasi 3-tahap saat rebooking diproses (Waiver Tarif, Pengalihan Bagasi, Penerbitan Kursi/Gate).
         Di produksi: setiap tahap (rebookStep 1, 2, 3, 4) mewakili progress panggilan API backend ke GDS (POST /api/bookings/{pnr}/rebook).
     en: Displays 3-step animated modal while rebooking is processed (Tariff Waiver, Baggage Transfer, Seat/Gate Issuance).
         In production: each step (rebookStep 1, 2, 3, 4) represents backend API call progress to GDS (POST /api/bookings/{pnr}/rebook). --}}
<!-- Instant Aviation Rebooking Step Animation Modal (Figma & Enterprise UX Polish) -->
<div x-show="isRebookingProcess" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="bg-white rounded-xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-slate-200 space-y-4 text-left relative overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        {{-- id: Garis Progress Animasi di Bagian Atas Modal
             en: Animated Top Progress Line --}}
        <!-- Top Animated Progress Line -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-slate-100 overflow-hidden">
            <div class="h-full bg-brand-600 transition-all duration-300"
                 :style="'width: ' + (rebookStep === 1 ? '25%' : (rebookStep === 2 ? '55%' : (rebookStep === 3 ? '85%' : '100%')))"></div>
        </div>

        {{-- id: Header Modal Proses Rebooking
             en: Rebooking Process Modal Header --}}
        <!-- Header -->
        <div class="flex items-center gap-3 pt-1">
            <div class="w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center text-base shrink-0 border border-brand-100">
                <i class="fa-solid fa-plane-departure animate-pulse"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900"
                    x-text="lang === 'id' ? 'Memproses Tiket Pengganti' : 'Processing Rebooking'"></h3>
                <p class="text-[11px] text-slate-500"
                   x-text="lang === 'id' ? 'Sinkronisasi reservasi & PNR' : 'Syncing reservation & PNR'"></p>
            </div>
        </div>

        {{-- id: Daftar 3 Langkah Telemetri Rebooking
             en: 3 Rebooking Step Telemetry List --}}
        <!-- Real-Time Step Telemetry List -->
        <div class="space-y-2 py-1 text-xs">
            
            {{-- id: Langkah 1: Verifikasi Waiver Tarif Bebas Biaya (Waiver 72A)
                 en: Step 1: Disruption Tariff Waiver Verification (Rule 72A) --}}
            <!-- Step 1: Disruption Tariff Waiver Check -->
            <div class="p-2.5 rounded-lg border transition-all flex items-center justify-between"
                 :class="rebookStep >= 1 ? (rebookStep > 1 ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' : 'bg-blue-50/50 border-brand-300 text-brand-950') : 'bg-slate-50 border-slate-200/70 text-slate-400 opacity-60'">
                <div class="flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] shrink-0"
                         :class="rebookStep > 1 ? 'bg-emerald-500 text-white' : (rebookStep === 1 ? 'bg-brand-600 text-white animate-spin' : 'bg-slate-200 text-slate-400')">
                        <i :class="rebookStep > 1 ? 'fa-solid fa-check' : (rebookStep === 1 ? 'fa-solid fa-circle-notch' : 'fa-solid fa-1')"></i>
                    </div>
                    <div>
                        <div class="font-semibold" x-text="lang === 'id' ? 'Bebas Biaya Perubahan (Waiver 72A)' : 'Fee Waiver Verified (Rule 72A)'"></div>
                        <div class="text-[10px] text-slate-500" x-text="lang === 'id' ? 'Biaya tambahan Rp 0' : '$0 Penalty confirmed'"></div>
                    </div>
                </div>
                <span x-show="rebookStep > 1" class="text-[9px] font-bold text-emerald-700 uppercase bg-emerald-100 px-1.5 py-0.2 rounded">OK</span>
            </div>

            {{-- id: Langkah 2: Pengalihan Bagasi Otomatis ke Pesawat Baru
                 en: Step 2: Automatic Baggage Re-routing to New Aircraft --}}
            <!-- Step 2: Baggage Auto-Routing -->
            <div class="p-2.5 rounded-lg border transition-all flex items-center justify-between"
                 :class="rebookStep >= 2 ? (rebookStep > 2 ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' : 'bg-blue-50/50 border-brand-300 text-brand-950') : 'bg-slate-50 border-slate-200/70 text-slate-400 opacity-60'">
                <div class="flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] shrink-0"
                         :class="rebookStep > 2 ? 'bg-emerald-500 text-white' : (rebookStep === 2 ? 'bg-brand-600 text-white animate-spin' : 'bg-slate-200 text-slate-400')">
                        <i :class="rebookStep > 2 ? 'fa-solid fa-check' : (rebookStep === 2 ? 'fa-solid fa-circle-notch' : 'fa-solid fa-2')"></i>
                    </div>
                    <div>
                        <div class="font-semibold" x-text="lang === 'id' ? 'Pengalihan Bagasi Otomatis' : 'Automatic Baggage Transfer'"></div>
                        <div class="text-[10px] text-slate-500" x-text="lang === 'id' ? 'Tag bagasi ' + baggageTag + ' dialihkan ke ' + flight.alternative.flightNumber : 'Bag tag ' + baggageTag + ' routed to ' + flight.alternative.flightNumber"></div>
                    </div>
                </div>
                <span x-show="rebookStep > 2" class="text-[9px] font-bold text-emerald-700 uppercase bg-emerald-100 px-1.5 py-0.2 rounded">OK</span>
            </div>

            {{-- id: Langkah 3: Penerbitan Kursi & Boarding Pass Baru
                 en: Step 3: Seat Assignment & New Boarding Pass Issuance --}}
            <!-- Step 3: Boarding Pass & Seat Assignment -->
            <div class="p-2.5 rounded-lg border transition-all flex items-center justify-between"
                 :class="rebookStep >= 3 ? (rebookStep > 3 ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' : 'bg-blue-50/50 border-brand-300 text-brand-950') : 'bg-slate-50 border-slate-200/70 text-slate-400 opacity-60'">
                <div class="flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] shrink-0"
                         :class="rebookStep > 3 ? 'bg-emerald-500 text-white' : (rebookStep === 3 ? 'bg-brand-600 text-white animate-spin' : 'bg-slate-200 text-slate-400')">
                        <i :class="rebookStep > 3 ? 'fa-solid fa-check' : (rebookStep === 3 ? 'fa-solid fa-circle-notch' : 'fa-solid fa-3')"></i>
                    </div>
                    <div>
                        <div class="font-semibold" x-text="lang === 'id' ? 'Penerbitan Boarding Pass ' + flight.alternative.flightNumber : flight.alternative.flightNumber + ' Boarding Pass Issuance'"></div>
                        <div class="text-[10px] text-slate-500" x-text="lang === 'id' ? 'Kursi ' + activeFlight.seat + ' • Gate ' + (flight.alternative.gate || '-') : 'Seat ' + activeFlight.seat + ' • Gate ' + (flight.alternative.gate || '-')"></div>
                    </div>
                </div>
                <span x-show="rebookStep > 3" class="text-[9px] font-bold text-emerald-700 uppercase bg-emerald-100 px-1.5 py-0.2 rounded">OK</span>
            </div>

        </div>

        {{-- id: Notifikasi Status Telemetri di Bagian Bawah Modal
             en: Telemetry Status Notification at Modal Bottom --}}
        <!-- Telemetry Status Notice -->
        <div class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-100 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span x-text="rebookStep < 4 ? (lang === 'id' ? 'Sedang memproses tiket...' : 'Processing...') : (lang === 'id' ? 'Penerbangan berhasil dipindahkan!' : 'Rebooking complete!')"></span>
            </span>
            <span class="font-mono text-slate-400" x-text="activeFlight.pnr"></span>
        </div>

    </div>
</div>
