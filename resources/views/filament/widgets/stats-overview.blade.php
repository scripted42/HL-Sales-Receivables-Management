<x-filament-widgets::widget>
    <div class="space-y-6">
        
        <!-- ==================== Row 1: Welcome Name Card ==================== -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 p-6 sm:p-8 shadow-lg z-0">
            <!-- Background decorative circles -->
            <div class="absolute -top-12 -right-12 h-44 w-44 rounded-full bg-white/10 blur-xl"></div>
            <div class="absolute -bottom-8 right-24 h-32 w-32 rounded-full bg-white/5 blur-lg"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white leading-tight">
                        Selamat Datang Kembali, {{ $welcomeName }}!
                    </h2>
                    <p class="text-indigo-100 text-sm mt-1 max-w-xl">
                        Aplikasi HL Sales & Receivables siap membantu mengelola piutang, omzet lunas, laba bersih, serta status bonus pelanggan hari ini.
                    </p>
                </div>
                <!-- Quick Navigation Buttons (Senior Friendly, Clear Icons) -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ url('/admin/transactions/create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-sm rounded-xl shadow transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Buat Bon Baru
                    </a>
                    <a href="{{ url('/admin/customers/create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-500/30 text-white border border-white/20 hover:bg-indigo-500/40 font-semibold text-sm rounded-xl transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        Tambah Pelanggan
                    </a>
                </div>
            </div>
        </div>

        <!-- ==================== Row 2: Stats Cards with Sparklines ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Card 1: Total Piutang -->
            <div class="tabler-stats-card flex flex-col justify-between min-h-[140px]">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="stats-title">Total Piutang</span>
                        <!-- Trend indicator (Down/Up arrow based on Diff) -->
                        <span class="inline-flex items-center gap-0.5 text-xs font-bold {{ $piutangDiff <= 0 ? 'text-green-600 dark:text-green-400' : 'text-rose-600 dark:text-rose-400' }}">
                            @if($piutangDiff <= 0)
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m19 12-7 7-7-7"/><path d="M12 19V5"/></svg>
                                {{ abs(round($piutangDiff, 1)) }}%
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"/><path d="M12 5v14"/></svg>
                                {{ round($piutangDiff, 1) }}%
                            @endif
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                        {{ $piutangCount }} Bon belum lunas (outstanding)
                    </div>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-full mt-3 h-8">
                    <svg class="w-full h-full" viewBox="0 0 100 30" preserveAspectRatio="none">
                        <polyline points="{{ $piutangPoints }}" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: Total Omzet Lunas -->
            <div class="tabler-stats-card flex flex-col justify-between min-h-[140px]">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="stats-title">Total Omzet Lunas</span>
                        <span class="inline-flex items-center gap-0.5 text-xs font-bold {{ $omzetDiff >= 0 ? 'text-green-600 dark:text-green-400' : 'text-rose-600 dark:text-rose-400' }}">
                            @if($omzetDiff >= 0)
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"/><path d="M12 5v14"/></svg>
                                {{ round($omzetDiff, 1) }}%
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m19 12-7 7-7-7"/><path d="M12 19V5"/></svg>
                                {{ abs(round($omzetDiff, 1)) }}%
                            @endif
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                        LM: {{ number_format($omzetLM / 1000000, 1, ',', '.') }}jt | BR: {{ number_format($omzetBR / 1000000, 1, ',', '.') }}jt
                    </div>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-full mt-3 h-8">
                    <svg class="w-full h-full" viewBox="0 0 100 30" preserveAspectRatio="none">
                        <polyline points="{{ $omzetPoints }}" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: Total Laba HL Lunas -->
            <div class="tabler-stats-card flex flex-col justify-between min-h-[140px]">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="stats-title">Total Laba HL Lunas</span>
                        <span class="inline-flex items-center gap-0.5 text-xs font-bold {{ $labaDiff >= 0 ? 'text-green-600 dark:text-green-400' : 'text-rose-600 dark:text-rose-400' }}">
                            @if($labaDiff >= 0)
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"/><path d="M12 5v14"/></svg>
                                {{ round($labaDiff, 1) }}%
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m19 12-7 7-7-7"/><path d="M12 19V5"/></svg>
                                {{ abs(round($labaDiff, 1)) }}%
                            @endif
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        Rp {{ number_format($totalLaba, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                        LM: {{ number_format($labaLM / 1000000, 1, ',', '.') }}jt | BR: {{ number_format($labaBR / 1000000, 1, ',', '.') }}jt
                    </div>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-full mt-3 h-8">
                    <svg class="w-full h-full" viewBox="0 0 100 30" preserveAspectRatio="none">
                        <polyline points="{{ $labaPoints }}" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- Card 4: Bonus Eligibility -->
            <div class="tabler-stats-card flex flex-col justify-between min-h-[140px]">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="stats-title">Bonus Eligibility</span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 border border-green-200/50">
                            Aktif
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $customersWithBonusCount }} Pelanggan
                    </div>
                    <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                        Berhak klaim barang gratis (omzet tercapai)
                    </div>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-full mt-3 h-8">
                    <svg class="w-full h-full" viewBox="0 0 100 30" preserveAspectRatio="none">
                        <polyline points="{{ $bonusPoints }}" fill="none" stroke="#206bc4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

        </div>

        <!-- ==================== Row 3: Split Sections (Activity, Transactions, Invoices) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column: Last Activity Timeline (5 Columns) -->
            <div class="lg:col-span-5 bg-white dark:bg-gray-900 border border-gray-150 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-3 mb-5 flex items-center gap-2">
                    <div class="p-1.5 bg-indigo-50 dark:bg-indigo-950/50 rounded-lg text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Aktivitas Terakhir</h3>
                </div>
                
                <div class="relative pl-6 border-l-2 border-gray-200 dark:border-gray-800 space-y-6">
                    @forelse($recentActivities as $act)
                        <div class="relative">
                            <!-- Bullet Indicator Icon -->
                            <span class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-white dark:bg-gray-900 border-2 border-{{ $act['color'] === 'green' ? 'emerald' : ($act['color'] === 'blue' ? 'indigo' : $act['color']) }}-500 z-10">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $act['color'] === 'green' ? 'emerald' : ($act['color'] === 'blue' ? 'indigo' : $act['color']) }}-500"></span>
                            </span>
                            <!-- Time -->
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold block mb-0.5">
                                {{ $act['time']->diffForHumans() }}
                            </span>
                            <!-- Description -->
                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-normal">
                                {!! $act['description'] !!}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-xs">Belum ada aktivitas terbaru.</div>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Last Transactions & Last Invoices (7 Columns) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Card 1: Last Transactions -->
                <div class="bg-white dark:bg-gray-900 border border-gray-150 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-50 dark:bg-blue-950/50 rounded-lg text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Transaksi Terakhir</h3>
                        </div>
                        <a href="{{ url('/admin/transactions') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                            Lihat Semua
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-100 dark:border-gray-800 font-bold uppercase tracking-wider">
                                    <th class="py-2.5">Nomor Bon</th>
                                    <th class="py-2.5">Pelanggan</th>
                                    <th class="py-2.5 text-center">Status</th>
                                    <th class="py-2.5 text-right">Total Tagihan</th>
                                    <th class="py-2.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $t)
                                    <tr class="border-b border-gray-50 dark:border-gray-850 hover:bg-gray-50/50">
                                        <td class="py-3 font-mono font-bold text-gray-900 dark:text-white">{{ $t->nomor_bon }}</td>
                                        <td class="py-3 font-semibold text-gray-700 dark:text-gray-300">{{ $t->customer->name }}</td>
                                        <td class="py-3 text-center">
                                            @if($t->status === 'Lunas')
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200/50">LUNAS</span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/50">PIUTANG</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($t->total_owed, 0, ',', '.') }}</td>
                                        <td class="py-3 text-center">
                                            <a href="{{ url("/admin/transactions/{$t->id}/edit") }}" class="inline-flex items-center justify-center p-1 bg-gray-50 dark:bg-gray-800 text-gray-500 hover:text-indigo-600 rounded border border-gray-200 dark:border-gray-700 shadow-sm transition" title="Edit Transaksi">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-400">Belum ada transaksi terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card 2: Last Invoices (Download PDFs) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-150 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex items-center gap-2">
                        <div class="p-1.5 bg-emerald-50 dark:bg-emerald-950/50 rounded-lg text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Unduh Invoice (Bon) PDF Terbaru</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($recentInvoices as $inv)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-950 rounded-xl border border-gray-100 dark:border-gray-850 hover:bg-indigo-50/20 dark:hover:bg-indigo-950/10 transition">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-mono font-bold text-gray-900 dark:text-white">{{ $inv->nomor_bon }}</span>
                                        <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-semibold">{{ $inv->customer->name }} &bull; {{ $inv->tanggal->format('d M Y') }}</span>
                                    </div>
                                </div>
                                <a href="{{ url("/admin/transactions/{$inv->id}/pdf") }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 font-bold text-xs rounded-lg shadow-sm transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    Unduh PDF
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400 text-xs">Belum ada invoice untuk diunduh.</div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-filament-widgets::widget>
