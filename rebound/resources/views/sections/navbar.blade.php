{{-- id: Navbar utama \u2014 berisi logo REBOUND, tombol sidebar, navigasi desktop, pemilih bahasa (ID/EN), notifikasi, profil user
     en: Main navbar \u2014 contains REBOUND logo, sidebar toggle, desktop navigation, language picker (ID/EN), notifications, user profile
     #BACKEND id: Data profil user (nama, inisial, email) diambil dari currentUser yang sudah dari Auth. Notifikasi kini nyata dari tabel notifications.
     #BACKEND en: User profile data (name, initials, email) from currentUser already from Auth. Notifications now come from the real notifications table. --}}
<header class="h-[64px] sm:h-[56px] pt-2.5 sm:pt-0 bg-white border-b border-[#E2E8F0] px-3.5 sm:px-5 md:px-6 flex items-center justify-between z-30 shrink-0 select-none relative">
    
    <!-- LEFT: Hamburger (mobile) + Logo -->
    <div class="flex items-center gap-2">
        {{-- id: Tombol hamburger khusus mobile (<lg) untuk membuka panel sidebar kiri (daftar tiket PNR)
             sebagai overlay penuh via mobileTab = 'tickets'. Di desktop sidebar sudah tampil permanen.
             en: Mobile-only (<lg) hamburger button that opens the left sidebar (PNR ticket list) as a
             full overlay via mobileTab = 'tickets'. Desktop keeps the sidebar permanently visible. --}}
        <button @click="mobileTab = 'tickets'"
                class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition cursor-pointer shadow-2xs"
                :title="lang === 'id' ? 'Buka Daftar Tiket' : 'Open Ticket List'">
            <i class="fa-solid fa-bars text-sm"></i>
        </button>
        <a href="/" class="hover:opacity-90 transition">
            <x-logo size="sm" />
        </a>
    </div>

    <!-- CENTER: Main Navigation Tabs (Clean & Balanced, centered on iPad & Desktop) -->
    <nav class="hidden md:flex items-center gap-1 bg-[#F1F5F9] p-0.5 rounded-xl border border-slate-200/70">
        <!-- Assistant Tab -->
        <button @click="mobileTab = 'assistant'"
                :class="mobileTab === 'assistant' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/50 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                class="flex items-center gap-1.5 px-3.5 py-1.5 text-xs rounded-lg transition">
            <i class="fa-solid fa-robot text-brand-600 text-[11px]"></i>
            <span x-text="lang === 'id' ? 'Asisten AI' : 'Assistant'"></span>
        </button>

        <!-- My Trips Tab -->
        <button @click="showMyTripsModal = true"
                class="flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 rounded-lg hover:bg-white/60 transition">
            <i class="fa-solid fa-plane text-slate-500 text-[11px]"></i>
            <span x-text="lang === 'id' ? 'Perjalanan Saya' : 'My Trips'"></span>
        </button>
    </nav>

    <!-- RIGHT: Actions, Scenario Controller & User Profile -->
    <div class="flex items-center gap-2 sm:gap-2.5">
        
        <!-- id: Pill status penerbangan — hanya informasi (read-only). Status bersumber dari GDS mock per PNR;
             opsi simulasi manual dihapus karena bertentangan dengan alur data & bisa menampilkan state rebooked tanpa data penerbangan.
             en: Flight status pill — informational only (read-only). Status comes from the mock GDS per PNR;
             the manual simulation options were removed because they contradicted the data flow & could show a rebooked state without flight data. -->
        <div class="hidden xl:block">
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-semibold bg-slate-50 border-slate-200 text-slate-700"
                 :title="lang === 'id' ? 'Status dari GDS' : 'Status from GDS'">
                <span class="w-1.5 h-1.5 rounded-full" 
                      :class="flightStatus === 'on-time' ? 'bg-emerald-500' : (flightStatus === 'delayed' ? 'bg-amber-500' : 'bg-blue-500')"></span>
                <span x-text="flightStatus === 'on-time' ? (lang === 'id' ? 'Tepat Waktu' : 'On Time') : (flightStatus === 'delayed' ? (lang === 'id' ? 'Terlambat (+4j)' : 'Delayed (+4h)') : (lang === 'id' ? 'Terjadwal Baru' : 'Rebooked'))"></span>
            </div>
        </div>

        <!-- Language Switcher Dropdown (Clean Flags Only, Sharp & Compact) -->
        <div class="relative" x-data="{ langDropdownOpen: false }">
            <button @click="langDropdownOpen = !langDropdownOpen"
                    class="flex items-center gap-1.5 px-2 py-1 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs transition cursor-pointer"
                    :title="lang === 'id' ? 'Ganti Bahasa' : 'Switch Language'">
                
                <!-- Active Flag Indicator (EN / ID) -->
                <template x-if="lang === 'en'">
                    <svg class="w-5 h-3.5 rounded-[2px] shadow-2xs shrink-0 overflow-hidden border border-slate-200" viewBox="0 0 60 30">
                        <clipPath id="flag_uk_btn"><path d="M0,0 v30 h60 v-30 z"/></clipPath>
                        <g clip-path="url(#flag_uk_btn)">
                            <path d="M0,0 v30 h60 v-30 z" fill="#012169"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#C8102E" stroke-width="3.5"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/>
                        </g>
                    </svg>
                </template>
                <template x-if="lang === 'id'">
                    <svg class="w-5 h-3.5 rounded-[2px] shadow-2xs shrink-0 overflow-hidden border border-slate-200" viewBox="0 0 3 2">
                        <rect width="3" height="1" fill="#E70011"/>
                        <rect y="1" width="3" height="1" fill="#FFFFFF"/>
                    </svg>
                </template>

                <i class="fa-solid fa-chevron-down text-[8px] text-slate-400"></i>
            </button>

            <!-- Language Dropdown Menu (Flags Only, Minimalist & Crisp) -->
            <div x-show="langDropdownOpen" 
                 @click.away="langDropdownOpen = false" 
                 x-cloak
                 class="absolute right-0 mt-1 bg-white rounded-md shadow-md border border-slate-200 p-1 z-50 flex flex-col gap-1 min-w-[48px]">

                <!-- UK Flag Option -->
                <button @click="setLanguage('en'); langDropdownOpen = false" 
                        :class="lang === 'en' ? 'bg-blue-50 border border-brand-300' : 'border border-transparent hover:bg-slate-100'"
                        class="p-1.5 rounded-[4px] flex items-center justify-center transition cursor-pointer"
                        title="English">
                    <svg class="w-6 h-4 rounded-[2px] shadow-2xs shrink-0 overflow-hidden border border-slate-200" viewBox="0 0 60 30">
                        <clipPath id="flag_uk_menu"><path d="M0,0 v30 h60 v-30 z"/></clipPath>
                        <g clip-path="url(#flag_uk_menu)">
                            <path d="M0,0 v30 h60 v-30 z" fill="#012169"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#C8102E" stroke-width="3.5"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/>
                        </g>
                    </svg>
                </button>

                <!-- Indonesia Flag Option -->
                <button @click="setLanguage('id'); langDropdownOpen = false" 
                        :class="lang === 'id' ? 'bg-blue-50 border border-brand-300' : 'border border-transparent hover:bg-slate-100'"
                        class="p-1.5 rounded-[4px] flex items-center justify-center transition cursor-pointer"
                        title="Bahasa Indonesia">
                    <svg class="w-6 h-4 rounded-[2px] shadow-2xs shrink-0 overflow-hidden border border-slate-200" viewBox="0 0 3 2">
                        <rect width="3" height="1" fill="#E70011"/>
                        <rect y="1" width="3" height="1" fill="#FFFFFF"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Help & FAQ Guide Button -->
        <button @click="showHelpModal = true"
                class="hidden lg:flex w-7 h-7 rounded-md items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer border border-transparent hover:border-slate-200"
                :title="lang === 'id' ? 'Pusat Bantuan' : 'Help Guide'">
            <i class="fa-regular fa-circle-question text-xs"></i>
        </button>

        <!-- Notification Center Dropdown -->
        <div class="relative" x-data="{ notifOpen: false }">
            <button @click="notifOpen = !notifOpen"
                    class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition relative cursor-pointer border border-transparent hover:border-slate-200"
                    :title="t('notifications')">
                <i class="fa-regular fa-bell text-xs"></i>
                <span x-show="hasUnreadNotif" class="absolute top-1 right-1 w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
            </button>

            <!-- Notifications Dropdown Panel -->
            <div x-show="notifOpen" 
                 @click.away="notifOpen = false" 
                 x-cloak
                 class="absolute right-0 mt-1.5 w-72 sm:w-80 bg-white rounded-lg shadow-lg border border-slate-200 py-1.5 z-50 text-xs text-left">
                
                <!-- Notification Header -->
                <div class="px-3.5 py-1.5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-900" x-text="lang === 'id' ? 'Notifikasi Operasional' : 'Operational Alerts'"></span>
                        {{-- id: Jumlah notifikasi belum dibaca dihitung dari tabel notifications, bukan hardcode "3 Baru"
                             en: Unread count computed from the notifications table, not a hardcoded "3 New" --}}
                        <span x-show="hasUnreadNotif" x-cloak class="px-1.5 py-0.2 bg-amber-100 text-amber-800 font-bold text-[9px] rounded"
                              x-text="unreadNotifCount + (lang === 'id' ? ' Baru' : ' New')"></span>
                    </div>
                    <button @click="markAllNotificationsRead()" 
                            class="text-[10.5px] text-brand-600 hover:text-brand-700 font-semibold cursor-pointer"
                            x-text="lang === 'id' ? 'Tandai Dibaca' : 'Mark Read'">
                    </button>
                </div>

                {{-- id: Daftar notifikasi dinamis dari tabel notifications (dikirim route dashboard).
                     Ikon, warna, judul, isi, dan waktu relatif semuanya mengikuti data asli database.
                     en: Dynamic notification list from the notifications table (sent by the dashboard route).
                     Icon, color, title, body, and relative time all follow the real database data. --}}
                <!-- Notifications List -->
                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto custom-scrollbar">

                    <!-- Empty State -->
                    <template x-if="notifications.length === 0">
                        <div class="p-4 text-center text-slate-400 text-[11px] leading-relaxed"
                             x-text="lang === 'id' ? 'Belum ada notifikasi. Alert gangguan penerbangan akan muncul di sini.' : 'No notifications yet. Flight disruption alerts will appear here.'"></div>
                    </template>

                    <template x-for="notif in notifications" :key="notif.id">
                        <div class="p-2.5 hover:bg-slate-50 transition cursor-pointer space-y-0.5"
                             :class="notif.is_read ? 'opacity-60' : ''"
                             @click="notifOpen = false; if (notif.pnr_code && chatSessions.some(s => s.pnr_code === notif.pnr_code)) selectTicket(notif.pnr_code)">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold flex items-center gap-1" :class="notifMeta(notif.type).color">
                                    <i class="fa-solid" :class="notifMeta(notif.type).icon"></i>
                                    <span x-text="lang === 'id' ? notif.title_id : notif.title_en"></span>
                                </span>
                                <span class="text-[9px] text-slate-400 font-mono" x-text="notifTimeAgo(notif.created_at)"></span>
                            </div>
                            <p class="text-[11px] text-slate-700 font-medium leading-snug"
                               x-text="lang === 'id' ? notif.message_id : notif.message_en"></p>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        <!-- User Profile Avatar & Switcher -->
        <div class="relative">
            <button @click="showUserDropdown = !showUserDropdown"
                    class="w-7 h-7 rounded-md bg-brand-600 text-white flex items-center justify-center font-bold text-[11px] shadow-2xs cursor-pointer hover:bg-brand-700 transition"
                    :title="currentUser.name">
                <span x-text="currentUser.initials"></span>
            </button>

            <!-- User Switcher Dropdown -->
            <div x-show="showUserDropdown"
                 @click.away="showUserDropdown = false"
                 x-cloak
                 class="absolute right-0 mt-1.5 w-60 bg-white rounded-lg shadow-lg border border-slate-200 py-1.5 z-50 text-xs">
                
                <div class="px-3.5 py-1.5 border-b border-slate-100">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold" x-text="t('active_account')"></p>
                    <p class="font-bold text-slate-900 mt-0.5" x-text="currentUser.name"></p>
                    <p class="text-[10.5px] text-slate-500 truncate" x-text="currentUser.email"></p>
                </div>

                {{-- id: Status penerbangan untuk Tablet / Mobile / iPad — hanya informasi (read-only), sama seperti pill di desktop
                     en: Flight status for Tablet / Mobile / iPad — informational only (read-only), same as the desktop pill --}}
                <div class="px-3.5 py-1.5 border-b border-slate-100 xl:hidden">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1"
                       x-text="lang === 'id' ? 'Status Penerbangan:' : 'Flight Status:'"></p>
                    <div class="flex items-center gap-1.5 py-1 px-2 rounded-md border bg-slate-50 border-slate-200 text-[10.5px] font-semibold text-slate-700">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="flightStatus === 'on-time' ? 'bg-emerald-500' : (flightStatus === 'delayed' ? 'bg-amber-500' : 'bg-blue-500')"></span>
                        <span x-text="flightStatus === 'on-time' ? (lang === 'id' ? 'Tepat Waktu' : 'On Time') : (flightStatus === 'delayed' ? (lang === 'id' ? 'Terlambat (+4j)' : 'Delayed (+4h)') : (lang === 'id' ? 'Terjadwal Baru' : 'Rebooked'))"></span>
                    </div>
                </div>

                <!-- Logout Form -->
                <div class="border-t border-slate-100 pt-1 mt-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full text-left px-3.5 py-1.5 text-rose-600 hover:bg-rose-50 font-semibold flex items-center gap-2 transition cursor-pointer">
                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                            <span x-text="t('logout')"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
