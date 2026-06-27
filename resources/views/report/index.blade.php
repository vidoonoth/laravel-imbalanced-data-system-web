<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <span class="text-gray-900 hover:text-gray-900 text-[23px] font-semibold">Laporan</span>
    </x-slot>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('report.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-semibold">
                    Filter
                </button>
                <a href="{{ route('report.index') }}"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Traffic / Samples -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Total Log Traffic</p>
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($totalTraffic, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Baris log yang diproses</p>
        </div>

        <!-- Normal Total -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Data Normal</p>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-green-600">{{ number_format($normalTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($normalPercentage, 2, ',', '.') }}% dari total log</p>
        </div>

        <!-- Malware Total -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Terdeteksi Malware</p>
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-red-600">{{ number_format($malwareTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($malwarePercentage, 2, ',', '.') }}% dari total log</p>
        </div>
    </div>

    <!-- Daily stats & Suspicious IP section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Daily Detection Stats -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Statistik Deteksi Harian
                </h3>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 border-b border-gray-100">
                        <tr>
                            <th class="py-2.5 px-4 text-left font-semibold">Tanggal</th>
                            <th class="py-2.5 px-4 text-right font-semibold">Total Log</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-green-600">Normal</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-red-600">Malware</th>
                            <th class="py-2.5 px-4 text-right font-semibold text-red-600">% Malware</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($dailyStats as $stat)
                            @php
                                $total = (int) $stat->total_count;
                                $malware = (int) $stat->malware_count;
                                $malwarePct = $total > 0 ? ($malware / $total) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ Carbon\Carbon::parse($stat->date)->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 text-right">{{ number_format($total, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-green-600">{{ number_format((int) $stat->normal_count, 0, ',', '.') }}</td>
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

        <!-- Top IP Mencurigakan -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Top IP Mencurigakan
                </h3>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 border-b border-gray-100">
                        <tr>
                            <th class="py-2.5 px-4 text-left font-semibold">IP Address</th>
                            <th class="py-2.5 px-4 text-left font-semibold">Lokasi</th>
                            <th class="py-2.5 px-4 text-center font-semibold">Total Alert</th>
                            <th class="py-2.5 px-4 text-right font-semibold">Confidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($topSuspiciousIps as $ip)
                            @php
                                $location = $ip->location ?? ['label' => 'Lokasi tidak tersedia', 'source' => 'unavailable'];
                                $locationSource = $location['source'] ?? 'unavailable';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-semibold text-gray-800">
                                    <a href="{{ route('dashboard.ip-activity', ['ip' => $ip->source_ip]) }}" class="text-blue-600 hover:underline">
                                        {{ $ip->source_ip }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="truncate max-w-40" title="{{ $location['label'] }}">{{ $location['label'] }}</span>
                                        @if ($locationSource === 'api')
                                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[10px] rounded font-bold shrink-0">GeoIP</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-semibold">
                                        {{ number_format($ip->total, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold">
                                    {{ number_format(((float) $ip->avg_confidence) * 100, 2, ',', '.') }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500">Belum ada IP yang terdeteksi malware.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Detections / History -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Deteksi dalam Rentang Filter
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Waktu Deteksi</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Source IP</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Destination IP</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Protocol</th>
                        <th class="px-5 py-3 text-center font-semibold text-gray-700">Prediction</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-700">Confidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentDetections as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-800 whitespace-nowrap">
                                {{ $record->detected_at ? $record->detected_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') : $record->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-4 text-gray-800 whitespace-nowrap font-medium">
                                <a href="{{ route('dashboard.ip-activity', ['ip' => $record->source_ip]) }}" class="text-blue-600 hover:underline">
                                    {{ $record->source_ip ?? '-' }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                {{ $record->destination_ip ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                {{ $record->protocol ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($record->prediction === 1)
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded font-semibold">
                                        Malware
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded font-semibold">
                                        Normal
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-700">
                                {{ number_format(((float) $record->confidence) * 100, 2, ',', '.') }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                Belum ada riwayat deteksi yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($recentDetections, 'hasPages') && $recentDetections->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $recentDetections->links() }}
            </div>
        @endif
    </div>
</x-app-with-sidebar-layout>
