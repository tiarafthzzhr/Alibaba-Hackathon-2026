{{-- #BACKEND Verifikasi PNR Wajib / Mandatory PNR Verification Modal
     id: Modal aktivasi tiket pertama kali. Mengharuskan pengguna menginput kode PNR dan nama penumpang.
         Data validasi PNR diverifikasi ke API backend `POST /api/pnr/verify` dan dicocokkan ke tabel Mock GDS maskapai.
         Fitur pemindaian barcode telah dihapus untuk menghemat waktu pengembangan.
     en: Mandatory initial ticket activation modal. Requires users to input a PNR booking code and passenger name.
         PNR validation is verified against backend API `POST /api/pnr/verify` and checked against the airline Mock GDS table.
         The barcode scanning feature has been removed to save development time. --}}
<!-- Mandatory PNR Verification Modal -->
<div x-show="!hasSetupPnr || showAddTicketModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-data="{
             // id: State lokal untuk modal PNR
             // en: Local state for PNR onboarding modal
             pnrInput: '',
             passengerInput: '',
             isVerifying: false,
             errorMessage: null,
             errorTitle: null,
             isShaking: false,

             // id: clearError() — Mereset pesan error validasi PNR
             // en: clearError() — Resets PNR validation error message
             clearError() {
                 this.errorMessage = null;
                 this.errorTitle = null;
             },

             // id: triggerError(title, msg) — Menampilkan banner error dan animasi getar pada input
             // en: triggerError(title, msg) — Displays error banner and triggers shake animation on inputs
             triggerError(title, msg) {
                 this.errorTitle = title;
                 this.errorMessage = msg;
                 this.isShaking = true;
                 setTimeout(() => { this.isShaking = false; }, 400);
             },

             // id: closeModal() — Menutup modal via klik di luar area atau tombol ESC.
             //     Modal aktivasi pertama kali (mandatory) tidak dapat ditutup sampai PNR berhasil diverifikasi.
             // en: closeModal() — Closes the modal via outside click or ESC key.
             //     The mandatory first-time activation modal cannot be closed until a PNR is verified.
             closeModal() {
                 showAddTicketModal = false;
             },


             // id: submitPnr() — Memvalidasi dan mengaktifkan tiket berdasarkan PNR yang diinput
             // en: submitPnr() — Validates and activates ticket based on provided PNR code
             // id: Input manual diverifikasi secara asli ke GDS via POST /api/pnr/verify; jika Atlas menjawab valid,
             //     Laravel mencatat kode PNR + ID user yang login ke tabel user_pnrs di MySQL (rebound_db).
             // en: Manual input is verified against the real GDS via POST /api/pnr/verify; when Atlas answers valid,
             //     Laravel records the PNR code + logged-in user ID in the user_pnrs table in MySQL (rebound_db).
             submitPnr() {
                 this.clearError();
                 const pnr = (this.pnrInput || '').trim().toUpperCase();

                 if (!pnr) {
                     this.triggerError('PNR Wajib Diisi', 'Informasi tidak dapat diambil. Masukkan kode booking (PNR) Anda.');
                     return;
                 }

                 const passenger = (this.passengerInput || '').trim();

                 if (!passenger) {
                     this.triggerError('Nama Penumpang Wajib Diisi', 'Masukkan nama penumpang sesuai tiket Anda untuk verifikasi GDS.');
                     return;
                 }

                 this.isVerifying = true;

                 fetch('/api/pnr/verify', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                     body: JSON.stringify({ pnr: pnr, passenger: passenger })
                 })
                 .then(response => response.json().then(data => ({ response, data })))
                 .then(({ response, data }) => {
                     this.isVerifying = false;

                     if (response.ok && data.status === 'success') {
                         const pnrCode = (data.data && data.data.pnr_code) || pnr.replace(/[^A-Z0-9]/g, '');

                         hasSetupPnr = true;
                         showAddTicketModal = false;
                         localStorage.setItem('rebound_has_setup_pnr', 'true');
                         currentUser.name = passenger.replace(' MRS', '').replace(' MR', '');
                         // id: Tambahkan tiket baru ke daftar tiket DB agar langsung tampil saat modal dibuka lagi
                         // en: Add the new ticket to the DB ticket list so it shows up next time the modal opens
                         if (Array.isArray(userTickets) && !userTickets.some(ticket => ticket.pnr_code === pnrCode)) {
                             userTickets.unshift({ pnr_code: pnrCode, last_name: passenger, status: 'active' });
                         }
                         // id: Masukkan sesi chat baru (dibuat Laravel di tabel agent_chat_sessions) langsung ke
                         //     sidebar kiri tanpa perlu refresh halaman
                         // en: Insert the new chat session (created by Laravel in agent_chat_sessions) straight
                         //     into the left sidebar without a page refresh
                         if (Array.isArray(chatSessions) && data.data.session && !chatSessions.some(s => s.pnr_code === pnrCode)) {
                             chatSessions.unshift(data.data.session);
                         }
                         // id: flightProfiles/alternativeFlightsByPnr dirender server saat halaman dimuat, sehingga
                         //     PNR yang baru diverifikasi belum punya profil lengkap bila hanya memanggil selectTicket()
                         //     (gejalanya: seat/zone/gate/durasi tampil "-" sampai halaman di-refresh manual).
                         //     Reload halaman agar server merender ulang seluruh props dengan PNR baru tersebut.
                         // en: flightProfiles/alternativeFlightsByPnr are server-rendered at page load, so a freshly
                         //     verified PNR has no complete profile if we only call selectTicket() (symptom: seat/zone/
                         //     gate/duration show "-" until a manual refresh). Reload so the server re-renders every
                         //     prop with the new PNR included.
                         showToast(lang === 'id' ? 'PNR valid menurut GDS Atlas! Tiket ' + pnrCode + ' aktif.' : 'PNR valid per GDS Atlas! Ticket ' + pnrCode + ' active.');
                         setTimeout(() => window.location.reload(), 900);
                     } else {
                         // id: GDS Atlas menyatakan PNR tidak valid / tidak ditemukan
                         // en: The Atlas GDS declared the PNR invalid / not found
                         this.triggerError('PNR Tidak Valid', data.message || 'PNR tidak ditemukan di GDS Atlas. Cek ulang kode PNR dan nama penumpang Anda.');
                     }
                 })
                 .catch(() => {
                     this.isVerifying = false;
                     this.triggerError('Gangguan Sistem', 'Tidak dapat menghubungi sistem verifikasi GDS. Silakan coba lagi.');
                 });
             },

             // id: activateOnServer(ticketKey) — Menyimpan PNR yang terverifikasi ke database via POST /api/pnr/activate
             //     agar status aktivasi (hasSetupPnr) bertahan setelah halaman di-refresh, bukan hanya di localStorage.
             //     Saat Laravel menjawab sukses, kartu sesi chat yang baru dibuat langsung dimasukkan ke sidebar kiri
             //     (chatSessions) sehingga tampil seketika tanpa perlu refresh halaman.
             // en: activateOnServer(ticketKey) — Persists the verified PNR to the database via POST /api/pnr/activate
             //     so the activation state (hasSetupPnr) survives a page refresh, not just in localStorage.
             //     When Laravel answers success, the newly created chat session card is inserted straight into the
             //     left sidebar (chatSessions) so it shows up instantly without a page refresh.
             activateOnServer(ticketKey) {
                 fetch('/api/pnr/activate', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     },
                     body: JSON.stringify({
                         pnr_code: ticketKey,
                         last_name: (this.passengerInput || '').trim() || null,
                     })
                 })
                 .then(response => response.ok ? response.json() : null)
                 .then(data => {
                     if (data && data.status === 'success' && data.data && data.data.session) {
                         const session = data.data.session;
                         if (Array.isArray(chatSessions) && !chatSessions.some(s => s.pnr_code === session.pnr_code)) {
                             chatSessions.unshift(session);
                         }
                     }
                 })
                 .catch(() => {});
             },

             // id: activateDbTicket(ticket) — Mengaktifkan kembali tiket asli milik user yang diambil dari
             //     database (tabel user_pnrs); riwayat chat untuk tiket tersebut dimuat otomatis oleh selectTicket().
             // en: activateDbTicket(ticket) — Re-activates a real ticket belonging to the user taken from the
             //     database (user_pnrs table); its chat history is loaded automatically by selectTicket().
             activateDbTicket(ticket) {
                 this.clearError();
                 this.pnrInput = ticket.pnr_code;
                 this.passengerInput = ticket.last_name || '';
                 hasSetupPnr = true;
                 showAddTicketModal = false;
                 localStorage.setItem('rebound_has_setup_pnr', 'true');
                 selectTicket(ticket.pnr_code);
                 this.activateOnServer(ticket.pnr_code);
                 showToast(lang === 'id' ? 'Tiket ' + ticket.pnr_code + ' aktif!' : 'Ticket ' + ticket.pnr_code + ' active!');
             }
         }"
         @click.self="closeModal()"
         @keydown.escape.window="closeModal()">

        <div class="bg-white rounded-xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-slate-200 text-left relative overflow-hidden space-y-3.5">

        {{-- id: Header Modal Aktivasi PNR
             en: PNR Activation Modal Header --}}
        <!-- Header -->
        <div class="flex items-center justify-between pb-1">
            <div>
                <h3 class="text-base font-bold text-slate-900"
                    x-text="lang === 'id' ? 'Aktivasi Tiket PNR' : 'Activate Ticket PNR'"></h3>
                <p class="text-xs text-slate-500 mt-0.5"
                   x-text="lang === 'id' ? 'Masukkan kode PNR untuk menampilkan jadwal.' : 'Enter PNR code to retrieve flight schedule.'"></p>
            </div>
            <button @click="showAddTicketModal = false"
                    x-show="hasSetupPnr || showAddTicketModal"
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer"
                    title="Tutup">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>


        <!-- Error Alert Callout Box -->
        <div x-show="errorMessage" x-cloak
             :class="{ 'animate-shake': isShaking }"
             class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-lg flex items-start gap-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm mt-0.5 shrink-0"></i>
            <div class="flex-1">
                <div class="font-bold text-rose-900 text-xs" x-text="errorTitle"></div>
                <div class="mt-0.5 text-[11.5px] text-rose-700 font-medium leading-relaxed" x-text="errorMessage"></div>
            </div>
            <button type="button" @click="clearError()" class="text-rose-400 hover:text-rose-700 cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        {{-- id: Formulir Input Manual — satu-satunya jalur aktivasi setelah fitur scan barcode dihapus
             en: Manual Input Form — the only activation path after the barcode scan feature was removed --}}
        <!-- Manual Input Form -->
        <div class="space-y-3">
            
            <!-- PNR Code Field -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Booking (PNR)</label>
                <div class="relative">
                    <i class="fa-solid fa-ticket text-slate-400 text-xs absolute left-3 top-3"></i>
                    <input type="text" 
                           x-model="pnrInput"
                           @input="clearError()"
                           placeholder="Contoh: SQ-951A atau GA-9821A" 
                           :class="errorMessage ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 ring-1 ring-rose-400/20' : 'border-slate-300 bg-slate-50 focus:bg-white focus:border-brand-600'"
                           class="w-full border rounded-lg pl-9 pr-3 py-2 text-xs font-mono uppercase font-bold text-slate-900 focus:outline-none transition">
                </div>
            </div>

            <!-- Passenger Name Field (Optional) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penumpang</label>
                <div class="relative">
                    <i class="fa-regular fa-user text-slate-400 text-xs absolute left-3 top-3"></i>
                    <input type="text" 
                           x-model="passengerInput"
                           placeholder="Contoh: ISTIQOMAH ASSYFA"
                           class="w-full border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-xs font-medium text-slate-900 bg-slate-50 focus:bg-white focus:outline-none focus:border-brand-600 transition">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-1">
                <button type="button"
                        @click="submitPnr()" 
                        :disabled="isVerifying || !pnrInput"
                        class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white rounded-lg font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="!isVerifying" class="flex items-center gap-1">
                        <span>Verifikasi & Tampilkan Tiket</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                    <span x-show="isVerifying" x-cloak class="flex items-center gap-1.5 text-sky-200">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <span>Memverifikasi GDS...</span>
                    </span>
                </button>
            </div>

        </div>

        {{-- id: Daftar Tiket dari Database — menggantikan skenario "Uji Coba Tiket" statis. Data diambil dari
             tabel user_pnrs milik user yang login (diteruskan route dashboard sebagai $userTickets).
             en: Database Ticket List — replaces the static "Test Scenarios". Data comes from the logged-in
             user's user_pnrs table (passed by the dashboard route as $userTickets). --}}
        <!-- User Tickets from Database -->
        <div class="pt-2 border-t border-slate-100">
            <div class="text-[10px] font-semibold text-slate-400 mb-1.5"
                 x-text="lang === 'id' ? 'Tiket Anda dari Database:' : 'Your Tickets from Database:'"></div>

            <template x-if="userTickets.length === 0">
                <p class="text-[10.5px] text-slate-400 bg-slate-50 border border-slate-200/70 rounded-lg p-2 leading-relaxed"
                   x-text="lang === 'id' ? 'Belum ada tiket tersimpan. Masukkan kode PNR Anda di atas.' : 'No saved tickets yet. Enter your PNR code above.'"></p>
            </template>

            <div class="space-y-1.5">
                <template x-for="ticket in userTickets" :key="ticket.pnr_code">
                    <button type="button"
                            @click="activateDbTicket(ticket)"
                            class="w-full p-2 rounded-lg border border-blue-200 bg-blue-50/50 hover:bg-blue-50 text-left text-xs transition cursor-pointer flex items-center justify-between group">
                        <div>
                            <div class="font-bold text-[11px] text-brand-900 flex items-center gap-1.5">
                                <span x-text="ticket.pnr_code"></span>
                                <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9px] font-bold rounded"
                                      x-show="ticket.status === 'active'"
                                      x-text="lang === 'id' ? 'Aktif' : 'Active'"></span>
                            </div>
                            <div class="text-[10px] text-slate-500" x-text="ticket.last_name"></div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-brand-600 text-[10px]"></i>
                    </button>
                </template>
            </div>
        </div>

    </div>
</div>
