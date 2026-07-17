<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <span class="text-gray-900 dark:text-gray-100 hover:text-gray-900 text-[23px] font-semibold">Laporan Deteksi</span>
    </x-slot>

    <style>
        .report-date-input {
            color-scheme: light;
        }

        .dark .report-date-input {
            color-scheme: dark;
        }

        .dark .report-date-input::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.7;
        }

        /* Table responsive adjustments */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>

    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <form method="GET" action="{{ route('report.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}"
                        class="report-date-input w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Akhir</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}"
                        class="report-date-input w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-gray-800 dark:bg-blue-600 text-white rounded-lg hover:bg-gray-900 dark:hover:bg-blue-700 transition font-semibold">
                        Filter
                    </button>
                    <a href="{{ route('report.index') }}"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition font-semibold text-center">
                        Reset
                    </a>
                </div>
            </div>

            <div class="@if($showReport) flex justify-end @else hidden @endif">
                <a href="{{ route('report.export.pdf', $filters) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                </a>
            </div>
        </form>
    </div>

    @if ($showReport)
        @php
            // This is a placeholder as the php block is needed
            // but the variables for the donut chart have been removed
        @endphp

        @if ($totalTraffic === 0)
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-5">
                <p class="font-semibold text-blue-900 dark:text-blue-300">Belum ada data deteksi dalam rentang tanggal ini</p>
            </div>
        @endif

        <!-- Summary Statistics Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Ringkasan Statistik</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Kategori</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Jumlah Record</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr>
                            <td class="px-5 py-3 text-gray-800 dark:text-gray-200 font-medium">Total Data Traffic</td>
                            <td class="px-5 py-3 text-right text-gray-800 dark:text-gray-200 font-bold">{{ number_format($totalTraffic, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-400">100.00%</td>
                        </tr>
                        <tr class="bg-green-50/30 dark:bg-green-900/10">
                            <td class="px-5 py-3 text-green-700 dark:text-green-400 font-medium">Data Normal</td>
                            <td class="px-5 py-3 text-right text-green-700 dark:text-green-400 font-bold">{{ number_format($normalTotal, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-green-600 dark:text-green-400">{{ number_format($normalPercentage, 2, ',', '.') }}%</td>
                        </tr>
                        <tr class="bg-red-50/30 dark:bg-red-900/10">
                            <td class="px-5 py-3 text-red-700 dark:text-red-400 font-medium">Terdeteksi Malware</td>
                            <td class="px-5 py-3 text-right text-red-700 dark:text-red-400 font-bold">{{ number_format($malwareTotal, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-red-600 dark:text-red-400">{{ number_format($malwarePercentage, 2, ',', '.') }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Statistik Deteksi -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Statistik Deteksi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="py-2.5 px-4 text-left font-semibold text-gray-700 dark:text-gray-300">Tanggal</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-gray-700 dark:text-gray-300">Total Log</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-green-600">Normal</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-red-600">Malware</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-red-600">% Malware</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-300">
                        @forelse ($dailyStats as $stat)
                            @php
                                $total = (int) $stat->total_count;
                                $malware = (int) $stat->malware_count;
                                $malwarePct = $total > 0 ? ($malware / $total) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="py-3 px-4 font-medium">{{ Carbon\Carbon::parse($stat->date)->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 text-right">{{ number_format($total, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-green-600 font-semibold">{{ number_format((int) $stat->normal_count, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-red-600 font-semibold">{{ number_format($malware, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-red-600 font-semibold">{{ number_format($malwarePct, 2, ',', '.') }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">Belum ada data harian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top IP Mencurigakan List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Top IP Mencurigakan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-800 dark:text-gray-300">No</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-800 dark:text-gray-300">IP Address</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-800 dark:text-gray-300">Lokasi</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-300">Total Alert</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($topSuspiciousIps as $index => $ip)
                            @php
                                $location = $ip->location ?? ['label' => 'Lokasi tidak tersedia', 'source' => 'unavailable'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-5 py-3 text-gray-800 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-5 py-3 text-gray-800 dark:text-gray-200 font-medium">
                                    <a href="{{ route('dashboard.ip-activity', ['ip' => $ip->source_ip]) }}" class="text-gray-800 dark:text-gray-200 hover:underline">
                                        {{ $ip->source_ip }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-gray-800 dark:text-gray-300">{{ $location['label'] }}</td>
                                <td class="px-5 py-3 text-right text-red-600 dark:text-red-400 font-bold">{{ number_format($ip->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-gray-800 dark:text-gray-400">Belum ada IP yang terdeteksi malware.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-with-sidebar-layout>
