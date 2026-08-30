<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REBOUND - AI Flight Assistant & Smart Rebooking</title>

    <!-- Favicon: REBOUND Aviation Emblem -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563EB">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="REBOUND">
    <meta name="description"
        content="REBOUND - Enterprise AI Passenger Assistant for Instant Flight Disruption Monitoring, Ticket Policy Waiver Verification & One-Click GDS Rebooking.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS Play CDN (No Vite Required) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Instrument Sans"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                    },
                    boxShadow: {
                        'soft': '0 2px 12px -3px rgba(0, 0, 0, 0.04), 0 3px 5px -2px rgba(0, 0, 0, 0.02)',
                        'card': '0 4px 16px -2px rgba(15, 23, 42, 0.05)',
                        'floating': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for Seamless Reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- JsBarcode CDN for 100% Real Scannable Barcodes (IATA / Code128) -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .chat-bubble-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }
    </style>
</head>

<body
    class="h-full font-sans antialiased text-[#1E293B] bg-[#F8FAFC] flex flex-col selection:bg-brand-500 selection:text-white"
    x-data="reboundApp()">

    {{-- id: Navbar atas — berisi logo, navigasi utama, pemilih bahasa, notifikasi, profil user
    en: Top navbar — contains logo, main navigation, language picker, notifications, user profile --}}
    <!-- Top App Bar Navigation -->
    @include('sections.navbar')

    <!-- Main Content Workspace -->
    <main class="flex-1 flex overflow-hidden">
        @yield('content')
    </main>

    {{-- id: Modal wajib input PNR pertama kali + scanner barcode tiket fisik. Lihat #BACKEND di file ini.
    en: Mandatory first-time PNR input modal + physical ticket barcode scanner. See #BACKEND in file. --}}
    <!-- First-Time Access PNR Onboarding & Physical Ticket Scanner Modal -->
    @include('sections.pnr-onboarding-modal')

    {{-- id: Modal daftar perjalanan — data perjalanan harus dari database bookings user
    en: Trips list modal — trip data must come from user's bookings database --}}
    <!-- Modal My Trips -->
    @include('sections.my-trips-modal')

    <!-- Help & FAQ Guide Modal -->
    @include('sections.help-modal')

    {{-- id: Modal QR code besar untuk di-scan di bandara — QR harus dari data booking real
    en: Large QR code modal for airport scanning — QR must be from real booking data --}}
    <!-- Scannable Large QR Code Modal -->
    @include('sections.qr-modal')

    {{-- id: Modal pilih wallet digital — integrasi Apple PassKit + Google Wallet API dari server
    en: Digital wallet selector modal — Apple PassKit + Google Wallet API integration from server --}}
    <!-- Universal Mobile Wallet Selector Modal (Android Google Wallet + iOS Apple Wallet) -->
    @include('sections.wallet-modal')

    {{-- id: Modal animasi proses rebooking multi-step — di produksi setiap step memanggil API backend
    en: Multi-step rebooking process animation modal — in production each step calls backend API --}}
    <!-- Rebooking Process Animation Modal -->
    @include('sections.rebooking-modal')

    {{-- id: Modal daftar penerbangan alternatif dari GDS Atlas — data harus dari GDS API real-time
    en: GDS Atlas alternative flights list modal — data must come from real-time GDS API --}}
    <!-- Multi-Flight GDS Atlas Alternative Schedules Modal -->
    @include('sections.flight-options-modal')

    {{-- id: Bottom navigation bar khusus mobile — UI-only, tidak ada data backend
    en: Mobile-only bottom navigation bar — UI-only, no backend data needed --}}
    <!-- Smartphone Bottom Navigation Bar -->
    @include('sections.mobile-bottom-nav')

    <!-- Global Toast Notification Banner -->
    <div x-show="toast.visible" x-cloak x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
        class="fixed bottom-5 right-5 z-50 max-w-sm bg-slate-950 text-white p-3 rounded-lg shadow-lg border border-slate-700 flex items-center gap-2.5 text-xs">
        <div
            class="w-6 h-6 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
            <i class="fa-solid fa-circle-check text-xs"></i>
        </div>
        <div class="flex-1 font-medium leading-snug" x-text="toast.message"></div>
        <button @click="toast.visible = false" class="text-slate-400 hover:text-white p-1 cursor-pointer">
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>
    </div>

    {{-- id: Modal "Tambah Tiket Baru PNR" lama dihapus — fungsinya sudah digantikan oleh sections/pnr-onboarding-modal
    (flag showAddTicketModal yang sama memicu keduanya, sehingga dua overlay menumpuk dan tampilan highlight rusak).
    en: Removed legacy "Add New PNR Ticket" modal — its function is replaced by sections/pnr-onboarding-modal
    (the same showAddTicketModal flag drove both, stacking two overlays and breaking the highlight look). --}}

    <!-- Alpine.js Main State Management -->
    {{-- #BACKEND
    id: Seluruh state management ini adalah fungsi utama aplikasi REBOUND. Setiap property dan function di bawah ini
    perlu disambungkan ke backend Laravel + database + API eksternal.
    en: This entire state management is the core of REBOUND app. Every property and function below needs to be connected
    to Laravel backend + database + external APIs. --}}
    <script>
        // id: reboundApp() — Fungsi utama Alpine.js yang mengelola seluruh state frontend. Backend harus menyediakan data awal via Blade props atau API endpoint.
        // en: reboundApp() — Main Alpine.js function managing all frontend state. Backend must provide initial data via Blade props or API endpoints.
        function reboundApp() {
            return {
                // id: lang — Bahasa aktif user, disimpan di localStorage & disinkronkan ke session Laravel via /lang/{locale}
                // en: lang — Active user language, saved in localStorage & synced to Laravel session via /lang/{locale}
                lang: localStorage.getItem('rebound_lang') || '{{ App::getLocale() }}' || 'en',
                langOpen: false, // id: toggle dropdown bahasa | en: language dropdown toggle

                // id: mobileTab — Tab navigasi aktif di mobile (bawah layar)
                // en: mobileTab — Active mobile navigation tab (bottom nav)
                mobileTab: 'assistant',

                // #BACKEND id: flightStatus — Status penerbangan saat ini, harus diambil dari GDS real-time API, bukan hardcode
                // #BACKEND en: flightStatus — Current flight status, must be fetched from GDS real-time API, not hardcoded
                flightStatus: 'delayed',

                // id: activeSidebarTab — Tab aktif di sidebar kanan (overview/policy/schedule/receipts)
                // en: activeSidebarTab — Active tab in right sidebar (overview/policy/schedule/receipts)
                activeSidebarTab: 'overview',

                // id: Visibility state untuk sidebar, modal, dan UI toggles
                // en: Visibility state for sidebars, modals, and UI toggles
                sidebarOpen: true,
                leftSidebarOpen: true,
                showMyTripsModal: false,
                showHelpModal: false,
                showQrModal: false,
                showWalletModal: false,
                showFlightOptionsModal: false,

                // #BACKEND id: hasSetupPnr — Cek apakah user sudah pernah input PNR, saat ini pakai localStorage. Harus dicek dari database (users.has_setup_pnr)
                // #BACKEND en: hasSetupPnr — Checks if user has ever input PNR, currently uses localStorage. Must check from database (users.has_setup_pnr)
                // hasSetupPnr: (localStorage.getItem('rebound_has_setup_pnr') === 'true' ? true : false),
                // SESUDAH:
                hasSetupPnr: {{ isset($hasSetupPnr) && $hasSetupPnr ? 'true' : 'false' }},

                // id: notifications — notifikasi operasional asli milik user dari tabel notifications
                //     (dikirim route dashboard), dirender dropdown navbar menggantikan kartu alert statis.
                // en: notifications — the user's real operational notifications from the notifications table
                //     (sent by the dashboard route), rendered in the navbar dropdown instead of static alert cards.
                notifications: @json($notifications ?? []),

                // id: unreadNotifCount & hasUnreadNotif — dihitung langsung dari array notifications,
                //     sehingga badge lonceng & label "Baru" selalu sinkron dengan database.
                // en: unreadNotifCount & hasUnreadNotif — computed straight from the notifications array,
                //     so the bell badge & "New" label always stay in sync with the database.
                get unreadNotifCount() {
                    return this.notifications.filter(n => !n.is_read).length;
                },
                get hasUnreadNotif() {
                    return this.unreadNotifCount > 0;
                },

                // id: State untuk loading indicator download PDF & Wallet Pass
                // en: Loading state for PDF & Wallet Pass download indicators
                isDownloadingPdf: false,
                isDownloadingPkpass: false,

                // id: toast — Notifikasi toast global yang muncul di pojok kanan bawah
                // en: toast — Global toast notification shown at bottom-right corner
                toast: { visible: false, message: '' },

                // id: State modal dan UI interaksi
                // en: Modal and UI interaction states
                showAddTicketModal: false,
                showUserDropdown: false,
                isRebookingProcess: false,
                rebookStep: 1, // id: step animasi rebooking (1-4) | en: rebooking animation step (1-4)
                ticketSearch: '', // id: filter pencarian tiket di sidebar kiri | en: ticket search filter in left sidebar

                // #BACKEND id: selectedTicketId — ID tiket yang sedang dipilih, saat ini hardcode. Harus dari database bookings.id atau bookings.pnr
                // #BACKEND en: selectedTicketId — Currently selected ticket ID, currently hardcoded. Must be from database bookings.id or bookings.pnr
                // selectedTicketId: 'GA826',
                selectedTicketId: @json($activePnrCode ?? 'GA826'),

                // id: userTickets — daftar PNR asli milik user dari tabel user_pnrs (dikirim route dashboard),
                //     ditampilkan di modal aktivasi menggantikan skenario uji coba statis.
                // en: userTickets — the user's real PNRs from the user_pnrs table (sent by the dashboard route),
                //     shown in the activation modal instead of static test scenarios.
                userTickets: @json($userTickets ?? []),

                // id: chatSessions — daftar sesi chat AI agent asli milik user dari tabel agent_chat_sessions
                // en: chatSessions — real user AI agent chat sessions from agent_chat_sessions table
                chatSessions: @json($chatSessions ?? []),

                // id: filteredChatSessions — getter untuk memfilter riwayat chat berdasarkan pencarian (ticketSearch)
                // en: filteredChatSessions — getter to filter chat history by search query (ticketSearch)
                get filteredChatSessions() {
                    if (!this.ticketSearch || !this.ticketSearch.trim()) {
                        return this.chatSessions;
                    }
                    const q = this.ticketSearch.toLowerCase();
                    return this.chatSessions.filter(s =>
                        (s.pnr_code && s.pnr_code.toLowerCase().includes(q)) ||
                        (s.flight_number && s.flight_number.toLowerCase().includes(q)) ||
                        (s.from_code && s.from_code.toLowerCase().includes(q)) ||
                        (s.to_code && s.to_code.toLowerCase().includes(q)) ||
                        (s.last_message && s.last_message.toLowerCase().includes(q)) ||
                        (s.context_summary && s.context_summary.toLowerCase().includes(q))
                    );
                },

                // id: min40 — Helper hitung waktu boarding (40 menit sebelum keberangkatan) dari depTime 'HH:MM'
                // en: min40 — Helper to compute boarding time (40 minutes before departure) from 'HH:MM' depTime
                min40(t) {
                    const m = /^([0-9]{1,2}):([0-9]{2})$/.exec(t || '');
                    if (!m) return t || '';
                    let total = parseInt(m[1], 10) * 60 + parseInt(m[2], 10) - 40;
                    if (total < 0) total += 1440;
                    return String(Math.floor(total / 60)).padStart(2, '0') + ':' + String(total % 60).padStart(2, '0');
                },

                // id: activeFlight — SATU sumber data penerbangan yang sedang aktif ditampilkan (overview, receipts,
                //     boarding pass, QR, barcode, download). Otomatis beralih ke penerbangan hasil rebooking
                //     (mis. Batik Air ID7153) begitu flightStatus 'rebooked', sehingga UI tidak lagi menampilkan
                //     data lama Garuda setelah rebooking.
                // en: activeFlight — the SINGLE source of the flight currently on display (overview, receipts,
                //     boarding pass, QR, barcode, downloads). Automatically switches to the rebooked flight
                //     (e.g. Batik Air ID7153) once flightStatus is 'rebooked', so the UI no longer shows the
                //     old Garuda data after rebooking.
                get activeFlight() {
                    const rebooked = this.flightStatus === 'rebooked';
                    const f = rebooked ? this.flight.alternative : this.flight.original;
                    // id: Fallback berbasis data — jika field penerbangan aktif kosong (mis. rebooked tanpa
                    //     gate/kursi), ambil dari profil penerbangan asli; sisa kosong tampil '-' supaya
                    //     tidak ada lagi tanggal/kota fiktif (bug lama: '30 Nov', '09:30', '14A').
                    // en: Data-driven fallbacks — when the displayed flight lacks a field (e.g. a rebooked
                    //     flight without gate/seat), borrow it from the original flight profile; anything
                    //     still empty renders '-' so no fictitious dates/cities remain (old bug: '30 Nov', '09:30', '14A').
                    const o = this.flight.original || {};
                    return {
                        flightNumber: f.flightNumber || o.flightNumber || this.selectedTicketId || '-',
                        airline: f.airline || o.airline || '-',
                        airlineCode: f.airlineCode || o.airlineCode || '-',
                        aircraft: f.aircraft || (!rebooked && o.aircraft) || '-',
                        gate: f.gate || (!rebooked && o.gate) || '-',
                        depTime: f.depTime || o.depTime || '-',
                        arrTime: f.arrTime || o.arrTime || '-',
                        // id: Waktu boarding = 40 menit sebelum keberangkatan (standar tampilan aplikasi)
                        // en: Boarding time = 40 minutes before departure (app display standard)
                        boardingTime: (f.depTime || o.depTime) ? this.min40(f.depTime || o.depTime) : '-',
                        date: f.date || o.date || '-',
                        class: f.class || o.class || '-',
                        duration: f.duration || o.duration || '-',
                        durationEn: f.durationEn || o.durationEn || '-',
                        fromCode: f.fromCode || o.fromCode || '-',
                        toCode: f.toCode || o.toCode || '-',
                        fromCity: f.fromCity || o.fromCity || '-',
                        toCity: f.toCity || o.toCity || '-',
                        toCityEn: f.toCityEn || o.toCityEn || f.toCity || o.toCity || '-',
                        terminal: f.terminal || o.terminal || '-',
                        seat: f.seat || o.seat || '-',
                        zone: f.boardingGroup || o.boardingGroup || '-',
                        pnr: this.selectedTicketId || '-',
                    };
                },

                // id: chatInput — Teks input chat user yang sedang diketik
                // en: chatInput — User's current chat input text being typed
                chatInput: '',
                // id: isTyping — Indikator bahwa AI sedang mengetik respons
                // en: isTyping — Indicator that AI is currently typing a response
                isTyping: false,


                // id: baggageTag & eticketNumber — nilai turunan deterministik dari PNR tiket aktif,
                //     sehingga tag bagasi & nomor e-tiket tampil konsisten di chat, modal rebooking,
                //     dan semua file unduhan boarding pass (bukan lagi string beku #GA-489102 / GA-9821A).
                // en: baggageTag & eticketNumber — deterministic values derived from the active ticket PNR,
                //     so the baggage tag & e-ticket number stay consistent across chat, rebooking modals,
                //     and every boarding pass download (no more frozen #GA-489102 / GA-9821A strings).
                // #BACKEND id: Nanti harus diganti nomor tag bagasi & e-tiket asli dari database bookings
                // #BACKEND en: Later must be replaced by real baggage tag & e-ticket numbers from the bookings database
                get baggageTag() {
                    const seed = (this.selectedTicketId || 'GA826').toUpperCase().replace(/[^A-Z0-9]/g, '');
                    let hash = 7;
                    for (let i = 0; i < seed.length; i++) hash = (hash * 31 + seed.charCodeAt(i)) % 1000000;
                    return '#' + this.activeFlight.airlineCode + '-' + String(hash).padStart(6, '0');
                },
                get eticketNumber() {
                    const seed = (this.selectedTicketId || 'GA826').toUpperCase().replace(/[^A-Z0-9]/g, '');
                    let hash = 13;
                    for (let i = 0; i < seed.length; i++) hash = (hash * 31 + seed.charCodeAt(i)) % 1000000000;
                    return '126-' + String(hash).padStart(9, '0');
                },

                // #BACKEND Dynamic Context-Aware Prompt Suggestions (hybrid 2 lapis)
                // id: Lapis 1 — suggestionsByPnr kiriman backend dibangun dari data booking riil
                //     (status GDS + nomor penerbangan), tampil instan tanpa panggilan AI.
                //     Lapis 2 — refreshAiSuggestions() meminta Qwen merumuskan saran kontekstual
                //     secara async lalu menggantikan chip bila respons tiba (cache per PNR).
                // en: Layer 1 — suggestionsByPnr from the backend is built from real booking data
                //     (GDS status + flight number) and renders instantly with no AI call.
                //     Layer 2 — refreshAiSuggestions() asks Qwen for contextual suggestions async
                //     and swaps the chips once the response arrives (cached per PNR).
                get dynamicSuggestions() {
                    // id: Hasil Qwen (lapis 2) menang bila sudah tiba untuk tiket aktif
                    // en: Qwen output (layer 2) wins once it has arrived for the active ticket
                    const aiCached = this.aiSuggestionCache?.[this.selectedTicketId];
                    if (Array.isArray(aiCached) && aiCached.length > 0) {
                        return aiCached;
                    }

                    // id: Lapis 1 — saran berbasis aturan dari backend per PNR
                    // en: Layer 1 — rule-based suggestions from the backend per PNR
                    const backendSuggestions = this.suggestionsByPnr?.[this.selectedTicketId];
                    if (Array.isArray(backendSuggestions) && backendSuggestions.length > 0) {
                        return backendSuggestions;
                    }

                    // id: Tiket demo tanpa profil backend — skenario kurasi dipertahankan
                    // en: Demo tickets without backend profiles — curated scenarios preserved
                    if (this.selectedTicketId === 'GA826') {
                        if (this.flightStatus === 'rebooked') {
                            return [
                                { id: 'Lihat e-Boarding Pass baru penerbangan ' + this.flight.alternative.flightNumber, en: 'View new ' + this.flight.alternative.flightNumber + ' e-Boarding Pass' },
                                { id: 'Cek status pengalihan bagasi ' + this.baggageTag, en: 'Check baggage transfer status ' + this.baggageTag }
                            ];
                        }
                        return [
                            { id: 'Cek tiket penerbangan untuk besok pagi.', en: 'Check flight tickets for tomorrow morning.' },
                            { id: 'Tanyakan tentang kondisi cuaca di jadwal penerbangan Anda..', en: 'Ask about weather conditions affecting your flight..' },
                            { id: 'Bagaimana hak kompensasi & makanan saya?', en: 'What are my compensation and meal entitlements?' }
                        ];
                    }
                    if (this.selectedTicketId === 'SQ951') {
                        return [
                            { id: 'Lokasi Plaza Premium Lounge di Terminal 3', en: 'Plaza Premium Lounge location at Terminal 3' },
                            { id: 'Berapa batas berat bagasi kabin Business Class?', en: 'What is Business Class cabin baggage allowance?' }
                        ];
                    }
                    if (this.selectedTicketId === 'SQ638') {
                        return [
                            { id: 'Cek prakiraan cuaca di Haneda (HND)', en: 'Check weather forecast at Haneda (HND)' },
                            { id: 'Berapa batas berat bagasi kabin Singapore Airlines?', en: 'What is Singapore Airlines cabin baggage allowance?' }
                        ];
                    }
                    return [
                        { id: 'Cek status jadwal penerbangan saya', en: 'Check my flight schedule status' }
                    ];
                },

                // #BACKEND GDS Atlas Multi-Flight Inventory
                // id: Daftar penerbangan alternatif kini dipasok dari backend per PNR aktif, bukan hardcode di Alpine.
                // en: Alternative flight inventory is now supplied by backend per active PNR, not hardcoded in Alpine.
                alternativeFlightsByPnr: @json($alternativeFlightsByPnr ?? []),
                alternativeFlightsSourceByPnr: {},
                get alternativeFlightsList() {
                    return this.alternativeFlightsByPnr?.[this.selectedTicketId] || [];
                },
                get alternativeFlightsSource() {
                    return this.alternativeFlightsSourceByPnr?.[this.selectedTicketId] || 'rebound_demo_fallback';
                },

                async loadAlternativeFlights(pnrCode, flight) {
                    if (!pnrCode || !flight || this.alternativeFlightsSourceByPnr?.[pnrCode]) return;

                    const from = flight.fromCode;
                    const to = flight.toCode;
                    if (!from || !to) return;

                    try {
                        const response = await fetch(`/api/flights/alternatives?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const payload = await response.json();

                        if (response.ok && payload.status === 'success' && Array.isArray(payload.data)) {
                            this.alternativeFlightsByPnr = {
                                ...this.alternativeFlightsByPnr,
                                [pnrCode]: payload.data,
                            };
                            this.alternativeFlightsSourceByPnr = {
                                ...this.alternativeFlightsSourceByPnr,
                                [pnrCode]: payload.source || 'rebound_demo_fallback',
                            };
                        }
                    } catch (error) {
                        // Server-rendered demo inventory remains visible if the request fails.
                    }
                },

                // id: Saran prompt lapis 1 dari backend (rule-based per PNR) + cache saran lapis 2 dari Qwen
                // en: Layer-1 prompt suggestions from the backend (rule-based per PNR) + layer-2 Qwen suggestion cache
                suggestionsByPnr: @json($suggestionsByPnr ?? []),
                aiSuggestionCache: {},

                // id: Hasil rebooking dari tabel rebookings kiriman rute dashboard (PNR -> objek
                //     penerbangan alternatif). Dipakai restoreRebookedState() untuk memulihkan status
                //     'rebooked' dari database — menggantikan penyimpanan localStorage sepenuhnya.
                // en: Rebooking results from the rebookings table sent by the dashboard route (PNR ->
                //     alternative flight object). Used by restoreRebookedState() to restore the 'rebooked'
                //     status from the database — fully replacing the localStorage storage.
                rebookingsByPnr: @json($rebookingsByPnr ?? []),

                // id: translations — Katalog terjemahan dinamis kiriman rute dashboard: baris tabel
                //     translations (dikelola via POST /api/translations) menimpa nilai file lang statis,
                //     jadi teks UI bisa diubah dari database tanpa menyentuh file lang/id & lang/en.
                // en: translations — Dynamic translation catalogue sent by the dashboard route: rows in
                //     the translations table (managed via POST /api/translations) override the static lang
                //     file values, so UI copy can be edited from the database without touching lang/id & lang/en.
                translations: @json($translations ?? ['id' => [], 'en' => []]),

                // id: t(key) — Helper function untuk mengambil terjemahan berdasarkan bahasa aktif. Jika key tidak ditemukan, return key itu sendiri.
                // en: t(key) — Helper function to get translation based on active language. If key not found, returns the key itself.
                t(key) {
                    return (this.translations[this.lang] && this.translations[this.lang][key]) || key;
                },

                // id: init() — Lifecycle hook Alpine.js, dipanggil saat komponen pertama kali dimuat. Render barcode awal dan watch perubahan state.
                // en: init() — Alpine.js lifecycle hook, called when component first mounts. Renders initial barcode and watches state changes.
                init() {
                    this.$nextTick(() => {
                        this.renderBarcode();
                    });
                    // id: Watch perubahan state untuk re-render barcode otomatis
                    // en: Watch state changes to auto re-render barcode
                    this.$watch('flightStatus', () => this.renderBarcode());
                    this.$watch('selectedTicketId', () => this.renderBarcode());
                    this.$watch('activeSidebarTab', () => this.renderBarcode());
                    this.$watch('currentUser', () => this.renderBarcode());

                    // id: Muat riwayat percakapan & set tiket aktif dari sesi chat AI terbaru saat pertama kali login/dimuat
                    // en: Load chat history & set active ticket from the latest AI chat session upon initial login/load
                    if (this.hasSetupPnr && this.selectedTicketId) {
                        this.selectTicket(this.selectedTicketId);
                    }

                    // id: Pulihkan status rebooking dari localStorage agar hasil pindah jadwal bertahan setelah refresh
                    // en: Restore rebooking state from localStorage so the schedule change survives a page refresh
                    this.restoreRebookedState();

                },

                // id: renderBarcode() — Generate barcode Code128 yang bisa di-scan menggunakan library JsBarcode. Data barcode diambil dari nama penumpang + nomor penerbangan.
                // en: renderBarcode() — Generates scannable Code128 barcode using JsBarcode library. Barcode data is from passenger name + flight number.
                // #BACKEND id: Data barcode (flightNo, paxName, seat) harus dari database bookings, bukan hardcode
                // #BACKEND en: Barcode data (flightNo, paxName, seat) must come from bookings database, not hardcoded
                renderBarcode() {
                    this.$nextTick(() => {
                        const barcodeEl = document.getElementById('live-boarding-barcode');
                        if (barcodeEl && typeof JsBarcode !== 'undefined') {
                            // id: flightNo diambil dari activeFlight agar mengikuti penerbangan hasil rebooking
                            // en: flightNo comes from activeFlight so it follows the rebooked flight
                            const flightNo = this.activeFlight.flightNumber;
                            const paxName = (this.currentUser && this.currentUser.name) ? this.currentUser.name.replace(/[^A-Za-z]/g, '').slice(0, 8).toUpperCase() : 'ZAKARIA';
                            // id: Format barcode standar IATA BCBP (Bar Coded Boarding Pass)
                            // en: Standard IATA BCBP (Bar Coded Boarding Pass) format
                            const codeVal = `M1${paxName}-${flightNo}-${this.activeFlight.seat}`;
                            try {
                                JsBarcode(barcodeEl, codeVal, {
                                    format: "CODE128",
                                    lineColor: "#0f172a",
                                    width: 1.1,
                                    height: 16,
                                    displayValue: false,
                                    margin: 0
                                });
                            } catch (e) { }
                        }
                    });
                },

                // #BACKEND Authenticated Current User
                // id: Data user sudah dari Auth — tapi 'role' & 'passenger' title masih statis, harus diambil dari tabel users/profiles di database
                // en: User data is from Auth — but 'role' & 'passenger' title are still static, must be fetched from users/profiles table in database
                currentUser: {
                    id: {{ Auth::id() ?? 1 }},
                    name: @json(Auth::user()->name ?? 'Zakaria MP'),
                    initials: @json(strtoupper(substr(Auth::user()->name ?? 'ZM', 0, 2))),
                    email: @json(Auth::user()->email ?? 'zakariamp@rebound.ai'),
                    // passenger: @json((Auth::user()->name ?? 'Zakaria MP') . ' (MR)'), // #BACKEND id: title MR/MRS harus dari DB | en: MR/MRS title from DB
                    // role: 'Frequent Flyer Platinum' // #BACKEND id: role/tier loyalty dari database | en: loyalty tier from database
                    passenger: @json((Auth::user()->name ?? 'Zakaria MP') . ' (' . (Auth::user()->title ?? 'MR') . ')'),
                    role: @json(Auth::user()->loyalty_tier ?? 'Frequent Flyer Platinum')
                },

                // #BACKEND Flight Data State — now sourced from backend-provided booking profiles
                // id: Data penerbangan utama diambil dari mock GDS/backend untuk PNR aktif, bukan hardcode di Alpine.
                // en: The primary flight data comes from backend/mock GDS booking profiles for the active PNR, not hardcoded in Alpine.
                flightProfiles: @json($flightProfiles ?? []),
                flight: @json($activeFlightProfile) ?? {
                    original: {},
                    alternative: {
                        flightNumber: 'GA830',
                        airline: 'Garuda Indonesia',
                        airlineCode: 'GA',
                        aircraft: 'Boeing 737-800',
                        gate: '4A',
                        fromCity: 'Jakarta',
                        fromCode: 'CGK',
                        toCity: 'Singapura',
                        toCityEn: 'Singapore',
                        toCode: 'SIN',
                        depTime: '12:40',
                        arrTime: '15:25',
                        duration: '2j 45m',
                        durationEn: '2h 45m',
                        departureCountdownId: 'Berangkat 45 menit lagi',
                        departureCountdownEn: 'Departs in 45 min',
                    },
                    flightStatus: 'delayed'
                },

                // #BACKEND Chat Messages History
                // id: Pesan awal & riwayat chat harus diambil dari database messages + di-generate oleh AI API (Qwen) berdasarkan data penerbangan real-time
                // en: Initial message & chat history must be fetched from messages database + generated by AI API (Qwen) based on real-time flight data
                messages: [],

                // #BACKEND Select Ticket from Left Sidebar
                // id: Fungsi selectTicket() sekarang memprioritaskan profil backend per PNR, lalu fallback demo bila data belum ada.
                // en: selectTicket() now prioritizes backend booking profiles per PNR, then falls back to demo cases when no data exists.
                selectTicket(id) {
                    this.selectedTicketId = id;
                    // id: Di layar mobile (<1024px), memilih tiket dari panel "Tiket PNR" otomatis kembali ke
                    //     tab chat agar percakapan tiket terpilih langsung terlihat (panel tiket adalah overlay penuh).
                    // en: On mobile (<1024px), picking a ticket from the "PNR Tickets" panel automatically returns
                    //     to the chat tab so the selected ticket's conversation is shown at once (tickets panel is a full overlay).
                    if (window.matchMedia && !window.matchMedia('(min-width: 1024px)').matches) {
                        this.mobileTab = 'assistant';
                    }
                    const backendProfile = this.flightProfiles?.[id] || null;
                    if (backendProfile && backendProfile.original) {
                        this.flight = {
                            ...this.flight,
                            ...backendProfile,
                            original: { ...backendProfile.original },
                            alternative: { ...this.flight.alternative, ...(backendProfile.alternative || {}) },
                        };
                        this.flightStatus = backendProfile.flightStatus || this.flightStatus;
                        this.messages = [
                            {
                                sender: 'ai',
                                time: backendProfile.original.depTime || '09:35',
                                type: backendProfile.flightStatus === 'delayed' ? 'greeting' : 'text',
                                textId: `Halo ${this.currentUser.name}! Saya sedang memantau penerbangan ${backendProfile.original.flightNumber} Anda (${backendProfile.original.fromCode} → ${backendProfile.original.toCode}). Status saat ini ${backendProfile.original.statusId || 'terverifikasi'}.`,
                                textEn: `Hello ${this.currentUser.name}! I am monitoring your flight ${backendProfile.original.flightNumber} (${backendProfile.original.fromCode} → ${backendProfile.original.toCode}). Current status is ${backendProfile.original.statusEn || 'verified'}.`,
                                showRecommendation: backendProfile.flightStatus === 'delayed',
                            }
                        ];

                        this.loadChatHistory();
                        this.refreshAiSuggestions(id);
                        this.loadAlternativeFlights(id, backendProfile.original);
                        return;
                    }

                    // id: Case GA826 — Data penerbangan Garuda Indonesia yang terganggu (delayed). Semua data hardcode di bawah ini harus diambil dari API /api/bookings/GA826
                    // en: Case GA826 — Disrupted Garuda Indonesia flight data (delayed). All hardcoded data below must be fetched from API /api/bookings/GA826
                    if (id === 'GA826') {
                        this.flightStatus = 'delayed';
                        this.flight.original.flightNumber = 'GA826';
                        this.flight.original.fromCode = 'CGK';
                        this.flight.original.toCode = 'SIN';
                        this.flight.original.airline = 'Garuda Indonesia';
                        this.messages = [
                            {
                                sender: 'ai',
                                time: '09:35',
                                type: 'greeting',
                                textId: `Halo ${this.currentUser.name}! Saya sedang memantau penerbangan GA826 Anda ke Singapura. Saat ini penerbangan Anda mengalami keterlambatan 4 jam 25 menit akibat cuaca buruk. Saya sudah mulai memeriksa aturan tiket dan mencari penerbangan alternatif untuk Anda.`,
                                textEn: `Hello ${this.currentUser.name}! I'm monitoring your flight GA826 to Singapore. Your flight is currently delayed by 4 hours 25 minutes due to bad weather. I have begun checking ticket rules and finding alternative flights for you.`,
                                showRecommendation: true,
                            }
                        ];
                        this.loadAlternativeFlights(id, this.flight.original);
                        // id: Case SQ951 — Tiket Singapore Airlines Business Class (on-time). Data hardcode harus dari API /api/bookings/SQ951
                        // en: Case SQ951 — Singapore Airlines Business Class ticket (on-time). Hardcoded data must be from API /api/bookings/SQ951
                    } else if (id === 'SQ951') {
                        this.flightStatus = 'on-time';
                        this.flight.original = {
                            flightNumber: 'SQ951',
                            airline: 'Singapore Airlines',
                            airlineCode: 'SQ',
                            fromCity: 'Jakarta',
                            fromCode: 'CGK',
                            toCity: 'Singapura',
                            toCityEn: 'Singapore',
                            toCode: 'SIN',
                            date: '15 Okt',
                            dateFullId: '15 Oktober 2026, 05.00',
                            dateFullEn: '15 Oct 2026, 05:00 AM',
                            depTime: '05:00',
                            arrTime: '07:50',
                            class: 'Business Class (SQ KFLY)',
                            seat: (this.currentUser && this.currentUser.name && this.currentUser.name.toLowerCase().includes('maulana')) ? '23D' : '23A',
                            gate: '6',
                            terminal: '3',
                            boardingGroup: '2',
                            lounge: 'Plaza Premium Lounge',
                            statusId: 'Tepat Waktu',
                            statusEn: 'On Time',
                            delayTime: '-',
                            delayCauseId: 'Normal',
                            delayCauseEn: 'Normal',
                            changeAllowedId: 'Ya (Gratis)',
                            changeAllowedEn: 'Yes (Complimentary)',
                            feeAmountId: 'Rp 0',
                            feeAmountEn: '$0',
                            fareDiffId: 'Berlaku',
                            fareDiffEn: 'Applies',
                        };
                        this.messages = [
                            {
                                sender: 'ai',
                                time: '05:00',
                                type: 'greeting',
                                textId: `Halo ${this.currentUser.name}! Tiket Business Class Singapore Airlines SQ951 (Jakarta CGK → Singapura SIN) Anda telah terverifikasi. Keberangkatan dari Terminal 3, Gate 6 pukul 05:00 AM. Anda memiliki akses ke Plaza Premium Lounge.`,
                                textEn: `Hello ${this.currentUser.name}! Your Singapore Airlines SQ951 Business Class ticket (Jakarta CGK → Singapore SIN) is verified. Departing from Terminal 3, Gate 6 at 05:00 AM with Plaza Premium Lounge access.`,
                                showRecommendation: false,
                            }
                        ];
                        // id: Case SQ638 — Tiket Singapore Airlines ke Tokyo Haneda (on-time). Data hardcode harus dari API /api/bookings/SQ638
                        // en: Case SQ638 — Singapore Airlines ticket to Tokyo Haneda (on-time). Hardcoded data must be from API /api/bookings/SQ638
                    } else if (id === 'SQ638') {
                        this.flightStatus = 'on-time';
                        this.flight.original = {
                            flightNumber: 'SQ638',
                            airline: 'Singapore Airlines',
                            airlineCode: 'SQ',
                            fromCity: 'Singapura',
                            fromCode: 'SIN',
                            toCity: 'Tokyo',
                            toCityEn: 'Tokyo',
                            toCode: 'HND',
                            date: '05 Des',
                            dateFullId: '05 Desember 2026, 23.55',
                            dateFullEn: '05 Dec 2026, 11:55 PM',
                            depTime: '23:55',
                            arrTime: '07:30',
                            class: 'Economy (K)',
                            seat: '18C',
                            gate: 'B4',
                            terminal: '3',
                            boardingGroup: '4',
                            lounge: '-',
                            statusId: 'Tepat Waktu',
                            statusEn: 'On Time',
                            delayTime: '-',
                            delayCauseId: 'Normal',
                            delayCauseEn: 'Normal',
                            changeAllowedId: 'Ya',
                            changeAllowedEn: 'Yes',
                            feeAmountId: 'Rp750.000',
                            feeAmountEn: '$50',
                            fareDiffId: 'Berlaku',
                            fareDiffEn: 'Applies',
                        };
                        this.messages = [
                            {
                                sender: 'ai',
                                time: '11:00',
                                type: 'greeting',
                                textId: `Halo ${this.currentUser.name}! Saya memantau tiket SQ638 Anda ke Tokyo (Haneda) pada 05 Desember 2026. Penerbangan saat ini dijadwalkan tepat waktu pukul 23:55 dari Bandara Changi (Terminal 3).`,
                                textEn: `Hello ${this.currentUser.name}! I am tracking your ticket SQ638 to Tokyo (Haneda) on 05 Dec 2026. Flight is currently on time at 23:55 from Changi Airport (Terminal 3).`,
                                showRecommendation: false,
                            }
                        ];
                        // id: Case default — Trip yang sudah selesai. Tampilkan pesan bahwa perjalanan telah selesai.
                        // en: Case default — Completed trips. Show message that the trip has been completed.
                    } else {
                        this.flightStatus = 'on-time';
                        this.messages = [
                            {
                                sender: 'ai',
                                time: '10:00',
                                type: 'greeting',
                                textId: `Perjalanan ini telah selesai untuk ${this.currentUser.name}. Anda dapat melihat resi dan riwayat e-tiket pada tab Resi di sidebar kanan.`,
                                textEn: `This trip has been completed for ${this.currentUser.name}. You can view receipts and e-ticket history in the Receipts tab on the right sidebar.`,
                                showRecommendation: false,
                            }
                        ];
                    }

                    // id: Override status dengan data asli dari database — kartu sesi di chatSessions membawa
                    //     status tabel mock_gds_bookings (delayed/on_time/cancelled/flown) yang dikirim server,
                    //     sehingga badge status kartu atas mengikuti kondisi GDS yang sebenarnya dan PNR di luar
                    //     tiket demo tidak selalu jatuh ke default 'on-time'.
                    //     Tiket demo (GA826/SQ951/SQ638) sengaja tidak di-override karena skenario & teks
                    //     percakapannya sudah dikurasi manual; status 'rebooked' juga tidak ditimpa karena
                    //     berasal dari aksi rebooking user yang dipulihkan dari tabel rebookings di database.
                    // en: Override status with real database data — the session card in chatSessions carries the
                    //     mock_gds_bookings table status (delayed/on_time/cancelled/flown) sent by the server,
                    //     so the top card status badge follows the actual GDS condition and PNRs outside the
                    //     demo tickets no longer always fall back to the 'on-time' default.
                    //     Demo tickets (GA826/SQ951/SQ638) are intentionally left untouched because their
                    //     scenario & chat copy are manually curated; 'rebooked' is never overridden because it
                    //     comes from the user's rebooking action restored from the rebookings table in the database.
                    const demoTicketIds = ['GA826', 'SQ951', 'SQ638'];
                    const sessionCard = Array.isArray(this.chatSessions)
                        ? this.chatSessions.find(s => s.pnr_code === id)
                        : null;
                    if (!demoTicketIds.includes(id) && sessionCard && sessionCard.status && this.flightStatus !== 'rebooked') {
                        const dbStatusMap = {
                            delayed: 'delayed',
                            cancelled: 'cancelled',
                            on_time: 'on-time',
                            active: 'on-time',
                            flown: 'on-time',
                            completed: 'on-time',
                        };
                        if (dbStatusMap[sessionCard.status]) {
                            this.flightStatus = dbStatusMap[sessionCard.status];
                        }
                    }

                    // id: Muat riwayat chat tersimpan dari database untuk tiket ini — jika ada riwayat,
                    //     riwayat tersebut menggantikan greeting awal sehingga percakapan lama tetap lanjut.
                    // en: Load stored chat history from the database for this ticket — if history exists,
                    //     it replaces the initial greeting so the old conversation continues.
                    this.loadChatHistory();
                    this.refreshAiSuggestions(id);
                },

                // id: refreshAiSuggestions(pnr) — Lapis 2 saran prompt: panggil POST /api/chat/suggestions
                //     secara async; chip saran lapis-1 sudah tampil lebih dulu dan langsung digantikan
                //     begitu respons Qwen tiba. Hasil di-cache per PNR agar pindah tab tidak memicu
                //     panggilan ulang; guard selectedTicketId mencegah saran melompat ke tiket lain
                //     bila user berpindah tab sebelum respons datang.
                // en: refreshAiSuggestions(pnr) — Layer-2 prompt suggestions: async call to
                //     POST /api/chat/suggestions; the layer-1 chips are already visible and get swapped
                //     as soon as the Qwen response arrives. Results are cached per PNR so switching tabs
                //     does not refetch; the selectedTicketId guard prevents suggestions jumping onto
                //     another ticket if the user switches tabs before the response lands.
                refreshAiSuggestions(pnr) {
                    if (!pnr || this.aiSuggestionCache[pnr]) {
                        return;
                    }
                    fetch('/api/chat/suggestions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ pnr: pnr, lang: this.lang })
                    })
                    .then(response => response.ok ? response.json() : null)
                    .then(data => {
                        if (data && data.status === 'success' && Array.isArray(data.suggestions) && data.suggestions.length > 0) {
                            this.aiSuggestionCache[pnr] = data.suggestions;
                            if (this.selectedTicketId === pnr) {
                                this.dynamicSuggestions = data.suggestions;
                            }
                        }
                    })
                    .catch(() => {});
                },

                // id: setStatus(status) dihapus — status penerbangan kini murni informasi dari GDS mock per PNR;
                //     simulasi manual di navbar sudah tidak ada (lihat navbar.blade.php).
                // en: setStatus(status) removed — flight status is now purely informational from the mock GDS per PNR;
                //     the manual simulation in the navbar no longer exists (see navbar.blade.php).

                // id: markAllNotificationsRead() — Menandai seluruh notifikasi sebagai sudah dibaca:
                //     langsung di state lokal (badge lonceng hilang seketika) lalu dipersistenkan ke
                //     database via POST /api/notifications/read-all.
                // en: markAllNotificationsRead() — Marks every notification as read: instantly in local
                //     state (bell badge disappears at once), then persisted to the database via
                //     POST /api/notifications/read-all.
                markAllNotificationsRead() {
                    this.notifications.forEach(n => { n.is_read = true; });
                    fetch('/api/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).catch(() => { });
                },

                // id: notifTimeAgo(iso) — Waktu relatif notifikasi (Baru saja / Xm lalu / Xj lalu / Xh lalu)
                //     dihitung dari created_at asli database, menggantikan string statis "2m lalu".
                // en: notifTimeAgo(iso) — Relative notification time (Just now / Xm ago / Xh ago / Xd ago)
                //     computed from the real database created_at, replacing the static "2m ago" strings.
                notifTimeAgo(iso) {
                    if (!iso) return '';
                    const diff = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 1000));
                    const id = this.lang === 'id';
                    if (diff < 60) return id ? 'Baru saja' : 'Just now';
                    if (diff < 3600) { const m = Math.floor(diff / 60); return id ? m + 'm lalu' : m + 'm ago'; }
                    if (diff < 86400) { const h = Math.floor(diff / 3600); return id ? h + 'j lalu' : h + 'h ago'; }
                    const d = Math.floor(diff / 86400);
                    return id ? d + ' hari lalu' : d + 'd ago';
                },

                // id: notifMeta(type) — Ikon & warna per jenis alert (delay/cancelled/alternative/rebooked/baggage)
                //     agar dropdown navbar tetap konsisten secara visual dengan desain kartu lama.
                // en: notifMeta(type) — Icon & color per alert type (delay/cancelled/alternative/rebooked/baggage)
                //     so the navbar dropdown stays visually consistent with the old card design.
                notifMeta(type) {
                    const map = {
                        delay: { icon: 'fa-triangle-exclamation', color: 'text-amber-600' },
                        cancelled: { icon: 'fa-ban', color: 'text-rose-600' },
                        alternative: { icon: 'fa-plane-departure', color: 'text-brand-600' },
                        rebooked: { icon: 'fa-plane-circle-check', color: 'text-blue-600' },
                        baggage: { icon: 'fa-suitcase-rolling', color: 'text-emerald-600' },
                    };
                    return map[type] || { icon: 'fa-bell', color: 'text-slate-600' };
                },

                // id: setLanguage(l) — Mengubah bahasa aplikasi (id/en). Simpan ke localStorage dan kirim request ke backend Laravel untuk set session locale.
                // en: setLanguage(l) — Changes app language (id/en). Saves to localStorage and sends request to Laravel backend to set session locale.
                // #BACKEND id: Endpoint /lang/{locale} harus ada di routes/web.php untuk set App::setLocale()
                // #BACKEND en: Endpoint /lang/{locale} must exist in routes/web.php to set App::setLocale()
                async setLanguage(l) {
                    this.lang = l;
                    try {
                        localStorage.setItem('rebound_lang', l);
                        await fetch('/lang/' + l, { headers: { 'Accept': 'application/json' } });
                    } catch (e) { }
                    this.langOpen = false;
                },

                // id: selectCustomAlternative(altFlight) — Dipanggil saat user memilih penerbangan alternatif dari modal GDS Atlas. Set data penerbangan alternatif baru lalu jalankan proses rebooking.
                // en: selectCustomAlternative(altFlight) — Called when user selects an alternative flight from GDS Atlas modal. Sets new alternative flight data then runs rebooking process.
                // #BACKEND id: Data altFlight harus dari GDS Atlas API response, bukan dari array statis alternativeFlightsList
                // #BACKEND en: altFlight data must come from GDS Atlas API response, not from static alternativeFlightsList array
                selectCustomAlternative(altFlight) {
                    this.flight.alternative = {
                        flightNumber: altFlight.flightNumber,
                        airline: altFlight.airline,
                        airlineCode: altFlight.airlineCode,
                        fromCity: altFlight.fromCity,
                        fromCode: altFlight.fromCode,
                        toCity: altFlight.toCity,
                        toCityEn: altFlight.toCityEn,
                        toCode: altFlight.toCode,
                        depTime: altFlight.depTime,
                        arrTime: altFlight.arrTime,
                        duration: altFlight.duration,
                        durationEn: altFlight.durationEn,
                        // id: Gate & tipe pesawat ikut dipindahkan agar overview/receipts menampilkan data maskapai baru.
                        //     Tanpa fallback fiktif — hasil Atlas Sandbox tidak mengirim gate, biarkan getter menampilkan '-'.
                        // en: Gate & aircraft type are carried over so overview/receipts show the new airline data.
                        //     No fictitious fallback — Atlas Sandbox results carry no gate, let the getter render '-'.
                        gate: altFlight.gate || null,
                        aircraft: altFlight.aircraft || null,
                        departureCountdownId: 'Berangkat ' + altFlight.depTime + ' WIB',
                        departureCountdownEn: 'Departs at ' + altFlight.depTime,
                    };
                    this.rebookFlight(altFlight);
                },

                // id: persistRebookedState() — Menyimpan hasil rebooking ke database via POST /api/rebook
                //     (tabel rebookings, satu baris per user + PNR), menggantikan localStorage sehingga
                //     status rebooked bertahan lintas perangkat/browser & tercatat di server. Key localStorage
                //     lama ikut dibersihkan agar tidak ada sisa data lokal.
                // en: persistRebookedState() — Saves the rebooking result to the database via POST /api/rebook
                //     (rebookings table, one row per user + PNR), replacing localStorage so the rebooked status
                //     survives across devices/browsers & is recorded server-side. The old localStorage key is
                //     also cleaned up so no local leftover remains.
                persistRebookedState() {
                    try {
                        localStorage.removeItem('rebound_rebooked_' + this.selectedTicketId);
                    } catch (e) { }
                    fetch('/api/rebook', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ pnr: this.selectedTicketId, alternative: this.flight.alternative })
                    }).catch(() => { });
                },

                // id: restoreRebookedState() — Dipanggil di init() setelah selectTicket(); jika rebookingsByPnr
                //     kiriman server memuat rebooking untuk tiket aktif, pulihkan flight.alternative &
                //     flightStatus 'rebooked' dari database. Key localStorage warisan versi lama dibersihkan
                //     agar tidak ada data lokal tersisa.
                // en: restoreRebookedState() — Called in init() after selectTicket(); if the server-supplied
                //     rebookingsByPnr holds a rebooking for the active ticket, restores flight.alternative &
                //     the 'rebooked' flightStatus from the database. The legacy localStorage key from the old
                //     version is cleaned up so no local data remains.
                restoreRebookedState() {
                    try {
                        localStorage.removeItem('rebound_rebooked_' + this.selectedTicketId);
                    } catch (e) { }
                    const saved = this.rebookingsByPnr?.[this.selectedTicketId];
                    if (saved && saved.flightNumber) {
                        // id: Baris rebookings versi lama bisa tersimpan tanpa field bandara/durasi.
                        //     Gabungkan dengan jadwal GDSAtlas untuk nomor penerbangan yang sama agar
                        //     kartu rebooked selalu tampil lengkap (bandara asal/tujuan, jam, durasi).
                        // en: Legacy rebooking rows may have been saved without airport/duration fields.
                        //     Merge with the GDS Atlas schedule for the same flight number so the rebooked
                        //     card always renders complete (origin/destination airports, times, duration).
                        const gdsFlight = (this.alternativeFlightsByPnr?.[this.selectedTicketId] || [])
                            .find(f => f.flightNumber === saved.flightNumber) || {};
                        this.flight.alternative = { ...gdsFlight, ...saved };
                        this.flightStatus = 'rebooked';
                    }
                },

                // #BACKEND Rebook action with multi-step telemetry dispatch animation
                // id: Proses rebooking masih simulasi timeout — harus memanggil API backend /api/rebook untuk proses rebooking nyata di GDS
                // en: Rebooking process is still simulated timeout — must call backend API /api/rebook for real GDS rebooking process
                rebookFlight(targetFlight = null) {
                    const target = targetFlight || this.flight.alternative;
                    this.isRebookingProcess = true;
                    this.rebookStep = 1;

                    // id: Step 1 (700ms): Cek waiver tarif gangguan — di produksi: panggil API /api/rebook/check-waiver
                    // en: Step 1 (700ms): Disruption tariff waiver check — in production: call API /api/rebook/check-waiver
                    setTimeout(() => {
                        this.rebookStep = 2;
                    }, 700);

                    // id: Step 2 (1400ms): Transfer bagasi otomatis — di produksi: panggil API /api/rebook/transfer-baggage
                    // en: Step 2 (1400ms): Auto baggage transfer — in production: call API /api/rebook/transfer-baggage
                    setTimeout(() => {
                        this.rebookStep = 3;
                    }, 1400);

                    // id: Step 3 (2100ms): Boarding pass & seat assignment selesai — di produksi: panggil API /api/rebook/confirm
                    // en: Step 3 (2100ms): Boarding pass & seat assignment complete — in production: call API /api/rebook/confirm
                    setTimeout(() => {
                        this.rebookStep = 4;
                        setTimeout(() => {
                            this.isRebookingProcess = false;
                            this.flightStatus = 'rebooked';
                            // id: Simpan state rebooking agar tetap tampil setelah halaman di-refresh
                            // en: Persist rebooking state so it still shows after the page is refreshed
                            this.persistRebookedState();
                            // id: Otomatis pindah ke tab receipts untuk tampilkan boarding pass baru
                            // en: Automatically switch to receipts tab to show new boarding pass
                            this.activeSidebarTab = 'receipts';

                            // id: Push pesan user ke chat bahwa rebooking telah diminta
                            // en: Push user message to chat that rebooking was requested
                            this.messages.push({
                                sender: 'user',
                                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                                type: 'text',
                                textId: `Pindahkan saya ke penerbangan ${target.airline} (${target.flightNumber}).`,
                                textEn: `Transfer me to ${target.airline} (${target.flightNumber}).`,
                            });

                            this.scrollToBottom();
                            this.isTyping = true;
                            setTimeout(() => {
                                this.isTyping = false;
                                // id: Push konfirmasi rebooking sukses dari AI ke chat
                                // en: Push rebooking success confirmation from AI to chat
                                this.messages.push({
                                    sender: 'ai',
                                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                                    type: 'success_card',
                                    textId: `Pengalihan ke ${target.flightNumber} berhasil dikonfirmasi. Klausul bebas penalti (Waiver 72A) aktif dan e-Boarding Pass baru telah diterbitkan untuk ${this.currentUser.name}. Bagasi tag ${this.baggageTag} telah dialihkan ke pesawat baru.`,
                                    textEn: `Rebooking to ${target.flightNumber} confirmed. Disruption fee waiver (Rule 72A) applied and new e-Boarding Pass issued for ${this.currentUser.name}. Baggage tag ${this.baggageTag} has been routed to the new flight.`,
                                    showSuccess: true
                                });
                                this.scrollToBottom();
                                this.renderBarcode();
                            }, 400);
                        }, 600);
                    }, 2100);
                },

                // id: sendMessage(customText) — Mengirim pesan chat dari user. customText digunakan untuk saran prompt yang diklik, atau ambil dari chatInput.
                // en: sendMessage(customText) — Sends a user chat message. customText is used for clicked prompt suggestions, or takes from chatInput.
                // #BACKEND id: Pesan user harus dikirim ke API backend POST /api/chat/send, disimpan ke database messages, lalu respons AI dari Qwen API
                // #BACKEND en: User message must be sent to backend API POST /api/chat/send, saved to messages database, then AI response from Qwen API
                // id: loadChatHistory() — Mengambil riwayat chat tersimpan dari GET /api/chat/history untuk PNR aktif,
                //     lalu mengisinya ke array messages agar percakapan tetap ada setelah refresh.
                // en: loadChatHistory() — Fetches stored chat history from GET /api/chat/history for the active PNR,
                //     then fills the messages array so the conversation persists after refresh.
                async loadChatHistory() {
                    try {
                        const response = await fetch('/api/chat/history?pnr=' + encodeURIComponent(this.selectedTicketId), {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!response.ok) return;
                        const data = await response.json();
                        const history = (data.messages || []).map(m => ({
                            sender: m.sender,
                            time: m.time || '',
                            type: m.type || 'text',
                            textId: m.text,
                            textEn: m.text,
                            showTicketPolicy: m.showTicketPolicy || false,
                            showRecommendation: m.showRecommendation || false,
                        }));
                        // id: Jika ada riwayat tersimpan, gunakan sebagai isi percakapan (menggantikan greeting awal)
                        // en: If stored history exists, use it as the conversation content (replaces the initial greeting)
                        if (history.length > 0) {
                            this.messages = history;
                            this.scrollToBottom();
                        }
                    } catch (error) {
                        console.error('Gagal memuat riwayat chat:', error);
                    }
                },

                // id: deleteChatSession(sessionId, pnrCode) — Menghapus sesi percakapan dari database & UI
                // en: deleteChatSession(sessionId, pnrCode) — Deletes a chat session from database & UI
                async deleteChatSession(sessionId, pnrCode) {
                    if (!confirm(this.lang === 'id' ? `Apakah Anda yakin ingin menghapus riwayat chat untuk tiket PNR ${pnrCode}?` : `Are you sure you want to delete chat history for PNR ${pnrCode}?`)) {
                        return;
                    }

                    try {
                        const response = await fetch('/api/chat/session/' + sessionId, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            // Hapus dari array chatSessions lokal
                            this.chatSessions = this.chatSessions.filter(s => s.id !== sessionId);

                            this.showToast(this.lang === 'id' ? `Sesi chat PNR ${pnrCode} berhasil dihapus!` : `Chat session PNR ${pnrCode} deleted!`);

                            // Jika sesi yang dihapus adalah yang sedang dibuka, pindah ke sesi lain atau bersihkan chat
                            if (this.selectedTicketId === pnrCode) {
                                if (this.chatSessions.length > 0) {
                                    this.selectTicket(this.chatSessions[0].pnr_code);
                                } else {
                                    this.messages = [];
                                }
                            }
                        } else {
                            this.showToast(this.lang === 'id' ? 'Gagal menghapus sesi chat.' : 'Failed to delete chat session.');
                        }
                    } catch (error) {
                        console.error('Error deleting chat session:', error);
                        this.showToast(this.lang === 'id' ? 'Terjadi kesalahan sistem.' : 'A system error occurred.');
                    }
                },


                async sendMessage(customText = null) {
                    const text = customText || this.chatInput;
                    if (!text || text.trim() === '') return;

                    this.messages.push({
                        sender: 'user',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        type: 'text',
                        textId: text,
                        textEn: text
                    });

                    this.chatInput = '';
                    this.isTyping = true;
                    this.scrollToBottom();

                    // id: Respons AI nyata dari Qwen via backend Laravel (POST /api/chat/send) — tanpa mesin
                    //     simulasi; bila Qwen tidak tersedia backend membalas pesan gangguan jujur (HTTP 503).
                    // en: Real AI response from Qwen via the Laravel backend (POST /api/chat/send) — no
                    //     simulation engine; when Qwen is unavailable the backend replies with an honest
                    //     disruption message (HTTP 503).
                    try {
                        // 2. Tembak API Backend Laravel yang terhubung ke Qwen/Qoder
                        const response = await fetch('/api/chat/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                // Pastikan token Sanctum atau CSRF disertakan jika perlu
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ message: text, pnr: this.selectedTicketId, lang: this.lang })
                        });

                        const data = await response.json();

                        // 3. Masukkan respons AI asli ke dalam obrolan
                        this.messages.push({
                            sender: 'ai',
                            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                            type: data.type || 'text', // BIsa 'text', 'policy_card', dll.
                            textId: data.replyId,
                            textEn: data.replyEn,
                            showTicketPolicy: data.showTicketPolicy || false,
                            showRecommendation: data.showRecommendation || false,
                        });

                    } catch (error) {
                        console.error("Gagal menghubungi AI:", error);
                        this.showToast("Gagal menghubungi asisten AI. Periksa koneksi Anda.");
                    } finally {
                        this.isTyping = false;
                        this.scrollToBottom();
                    }
                },

                // #BACKEND Download Official PDF Boarding Pass
                // id: PDF boarding pass masih di-generate client-side — harus di-generate dari server (Laravel PDF) dengan data booking dari database
                // en: PDF boarding pass is still generated client-side — must be server-generated (Laravel PDF) with booking data from database
                downloadPdf() {
                    this.isDownloadingPdf = true;
                    setTimeout(() => {
                        this.isDownloadingPdf = false;
                        // id: Semua data diambil dari activeFlight agar PDF mengikuti penerbangan hasil rebooking (mis. Batik Air ID7153)
                        // en: All data comes from activeFlight so the PDF follows the rebooked flight (e.g. Batik Air ID7153)
                        const af = this.activeFlight;
                        const flightNo = af.flightNumber;
                        const airline = af.airline;
                        const gate = af.gate;
                        const boarding = af.boardingTime + ' WIB';

                        // id: Buka jendela baru untuk cetak boarding pass HTML. Di produksi: redirect ke GET /api/boarding-pass/{pnr}/pdf
                        // en: Open new window to print boarding pass HTML. In production: redirect to GET /api/boarding-pass/{pnr}/pdf
                        const printWindow = window.open('', '_blank');
                        if (printWindow) {
                            printWindow.document.write(`
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>E-Boarding Pass - ${flightNo} - ${this.currentUser.passenger}</title>
                                    <style>
                                        @page { size: A4 portrait; margin: 12mm; }
                                        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0f172a; margin: 0; padding: 20px; background: #f8fafc; }
                                        .ticket { max-width: 650px; margin: 0 auto; background: #fff; border: 2px solid #cbd5e1; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
                                        .header { background: #0B3B60; color: #fff; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
                                        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
                                        .header p { margin: 4px 0 0; font-size: 12px; color: #bae6fd; font-family: monospace; }
                                        .badge { background: rgba(52,211,153,0.2); border: 1px solid #34d399; color: #6ee7b7; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
                                        .body { padding: 24px; }
                                        .route { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px; }
                                        .city { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold; }
                                        .code { font-size: 32px; font-weight: 900; margin: 2px 0; color: #0f172a; }
                                        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; background: #f8fafc; padding: 14px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
                                        .grid div { text-align: center; }
                                        .grid .label { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; }
                                        .grid .val { font-size: 17px; font-weight: 800; color: #0f172a; margin-top: 2px; }
                                        .grid .val.gate { color: #0284c7; }
                                        .baggage { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; display: flex; justify-content: space-between; margin-bottom: 20px; }
                                        .stub { border-top: 2px dashed #cbd5e1; padding-top: 16px; display: flex; justify-content: space-between; align-items: center; }
                                        .qr img { width: 90px; height: 90px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px; }
                                        .footer { font-size: 11px; color: #94a3b8; text-align: center; margin-top: 24px; }
                                    </style>
                                </head>
                                <body>
                                    <div class="ticket">
                                        <div class="header">
                                            <div>
                                                <h1>${airline}</h1>
                                                <p>${flightNo} • ${af.aircraft.toUpperCase()} • ${af.class.toUpperCase()}</p>
                                            </div>
                                            <div class="badge">CONFIRMED / BOARDING</div>
                                        </div>
                                        <div class="body">
                                            <div class="route">
                                                <div>
                                                    <div class="city">FROM / DARI</div>
                                                    <div class="code">${af.fromCode}</div>
                                                    <div style="font-size:12px; font-weight:600;">${af.fromCity} (${af.fromCode})</div>
                                                </div>
                                                <div style="text-align:center;">
                                                    <div style="font-size:11px; font-weight:bold; color:#059669;">NON-STOP (${af.durationEn})</div>
                                                    <div style="font-size:14px; font-weight:bold; color:#0284c7; letter-spacing:2px;">DIRECT FLIGHT &rarr;</div>
                                                </div>
                                                <div style="text-align:right;">
                                                    <div class="city">TO / KE</div>
                                                    <div class="code">${af.toCode}</div>
                                                    <div style="font-size:12px; font-weight:600;">${af.toCityEn} (${af.toCode})</div>
                                                </div>
                                            </div>
                                            <div class="grid">
                                                <div><div class="label">GATE</div><div class="val gate">${gate}</div></div>
                                                <div><div class="label">BOARDING</div><div class="val">${boarding}</div></div>
                                                <div><div class="label">SEAT</div><div class="val">${af.seat}</div></div>
                                                <div><div class="label">ZONE</div><div class="val">${af.zone}</div></div>
                                            </div>
                                            <div class="baggage">
                                                <span>Baggage Tag: <strong>${this.baggageTag}</strong></span>
                                                <span style="color:#047857; font-weight:bold;">AUTO-TRANSFERRED</span>
                                            </div>
                                            <div class="stub">
                                                <div>
                                                    <div style="font-size:10px; color:#64748b; font-weight:bold;">PASSENGER / NAMA PENUMPANG</div>
                                                    <div style="font-size:14px; font-weight:bold; margin-top:2px;">${this.currentUser.passenger}</div>
                                                    <div style="font-size:11px; font-family:monospace; margin-top:4px; color:#0284c7;">PNR: ${af.pnr} • ETKT: ${this.eticketNumber}</div>
                                                    <div style="font-size:11px; color:#059669; font-weight:bold; margin-top:4px;">Disruption Waiver 72A (Fee $0 / Rp 0)</div>
                                                </div>
                                                <div class="qr">
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=REBOUND%20E-BOARDING%20PASS%20PNR%20${af.pnr}%20${encodeURIComponent(this.currentUser.passenger)}%20${flightNo}%20GATE%20${gate}%20SEAT%20${af.seat}" alt="QR Code">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="footer">REBOUND Smart Airline Dispatch System • IATA Barcode & QR Code Compliant</div>
                                    <script>window.onload = function() { window.print(); }<\/script>
                                </body>
                                </html>
                            `);
                            printWindow.document.close();
                        }

                        this.showToast(
                            this.lang === 'id'
                                ? `Dokumen PDF Resmi E-Boarding Pass ${flightNo} siap dicetak/disimpan!`
                                : `Official E-Boarding Pass PDF for ${flightNo} ready to save/print!`
                        );
                    }, 500);
                },

                // #BACKEND Download Digital Apple Wallet Pass (.pkpass)
                // id: File .pkpass masih dummy blob — harus di-generate dari server menggunakan Apple PassKit dengan sertifikat & data booking dari DB
                // en: .pkpass file is still a dummy blob — must be server-generated using Apple PassKit with certificate & booking data from DB
                downloadPkpass() {
                    this.isDownloadingPkpass = true;
                    setTimeout(() => {
                        this.isDownloadingPkpass = false;
                        const afp = this.activeFlight;
                        const flightNo = afp.flightNumber;
                        const filename = `BoardingPass-${flightNo}-${this.currentUser.name.replace(/\s+/g, '_')}.pkpass`;
                        const dummyBlob = new Blob([
                            `REBOUND AVIATION ELECTRONIC BOARDING PASS (.PKPASS)\n===================================================\nPassenger: ${this.currentUser.passenger}\nAirline: ${afp.airline}\nFlight: ${flightNo}\nRoute: ${afp.fromCode} -> ${afp.toCode}\nGate: ${afp.gate}\nSeat: ${afp.seat}\nZone: ${afp.zone}\nPNR: ${afp.pnr}\nStatus: CONFIRMED\nDisruption Fee Waiver 72A: Rp 0\nBarcode: M1${this.currentUser.passenger.replace(/[^A-Za-z]/g, '').slice(0, 8).toUpperCase()} E${flightNo} E${afp.fromCode}${afp.toCode}\n===================================================`
                        ], { type: 'application/vnd.apple.pkpass' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(dummyBlob);
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        this.showToast(
                            this.lang === 'id'
                                ? `Digital Apple Wallet Pass (.pkpass) ${flightNo} berhasil diunduh!`
                                : `Digital Apple Wallet Pass (.pkpass) for ${flightNo} downloaded!`
                        );
                    }, 500);
                },

                // #BACKEND Save to Google Wallet (Android Support)
                // id: Google Wallet masih toast saja — harus integrasi Google Wallet API dengan JWT pass dari server
                // en: Google Wallet is toast-only — must integrate Google Wallet API with JWT pass from server
                saveGoogleWallet() {
                    const flightNo = this.activeFlight.flightNumber;
                    this.showToast(
                        this.lang === 'id'
                            ? `Pass Digital ${flightNo} berhasil disinkronkan ke Google Wallet Android!`
                            : `Digital Pass ${flightNo} successfully added to Google Wallet Android!`
                    );
                },

                // id: downloadPassImage() — Download gambar QR code boarding pass resolusi tinggi ke galeri foto HP.
                // en: downloadPassImage() — Downloads high-resolution boarding pass QR code image to phone photo gallery.
                // #BACKEND id: QR code harus di-generate dari server dengan data booking dari database, bukan dari API pihak ketiga
                // #BACKEND en: QR code must be server-generated with booking data from database, not from third-party API
                downloadPassImage() {
                    // id: Data QR diambil dari activeFlight agar gambar mengikuti penerbangan hasil rebooking
                    // en: QR data comes from activeFlight so the image follows the rebooked flight
                    const af = this.activeFlight;
                    const flightNo = af.flightNumber;
                    const link = document.createElement('a');
                    link.href = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&margin=15&data=${encodeURIComponent('REBOUND PASS: PNR ' + af.pnr + ' | ' + this.currentUser.passenger + ' | ' + flightNo + ' ' + af.fromCode + '->' + af.toCode + ' | SEAT: ' + af.seat + ' | GATE: ' + af.gate)}`;
                    link.download = `BoardingPass-QR-${flightNo}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    this.showToast(
                        this.lang === 'id'
                            ? `Gambar Tiket & QR Code HD berhasil disimpan ke Galeri Foto HP!`
                            : `Boarding Pass & HD QR Image saved to Photo Gallery!`
                    );
                },

                // id: showToast(msg) — Menampilkan notifikasi toast di pojok kanan bawah selama 4 detik.
                // en: showToast(msg) — Shows a toast notification at bottom-right corner for 4 seconds.
                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => {
                        this.toast.visible = false;
                    }, 4000);
                },

                // id: scrollToBottom() — Scroll otomatis ke bawah container chat agar pesan terbaru terlihat.
                // en: scrollToBottom() — Auto-scrolls to bottom of chat container so latest message is visible.
                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('chat-messages-container');
                        if (container) {
                            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                        }
                    });
                }
            }
        }
    </script>
</body>

</html>
