<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <span class="text-gray-900 hover:text-gray-900 text-[23px] font-semibold">Dashboard Deteksi</span>
    </x-slot>

    @php
        $lastScanTime = $latestDetection?->detected_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-';
        $lastScanDate = $latestDetection?->detected_at?->timezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum ada deteksi';
        $circumference = 251.33;
        $normalArc = $totalTraffic > 0 ? ($normalPercentage / 100) * $circumference : 0;
        $malwareArc = $totalTraffic > 0 ? ($malwarePercentage / 100) * $circumference : 0;
    @endphp

    @if ($totalTraffic === 0)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-blue-900">Belum ada data deteksi </p>                   
                </div>                
            </div>
        </div>
    @endif

    <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr));">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Total Data Traffic</p>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalTraffic, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">data terdeteksi</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Normal</p>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($normalTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($normalPercentage, 2, ',', '.') }}%</p>
        </div>

        @if ($canViewDashboardDetectionCard)
            <div class="bg-white rounded-lg border border-gray-200 p-6" data-dashboard-card="detection">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-600 text-sm font-medium">Malware</p>
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($malwareTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($malwarePercentage, 2, ',', '.') }}%</p>
            </div>
        @endif

        @if ($canViewDashboardSuspiciousIpCard)
            <div class="bg-white rounded-lg border border-gray-200 p-6" data-dashboard-card="suspicious-ip">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-600 text-sm font-medium">IP Mencurigakan</p>
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($suspiciousIpCount, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">source IP malware</p>
            </div>
        @endif

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Deteksi Terakhir</p>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $lastScanTime }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lastScanDate }} WIB</p>
        </div>
    </div>

    <div class="grid gap-6 mb-6 items-start" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));">

        @if ($canViewDashboardDetectionCard)
            <div class="bg-white rounded-lg border border-gray-200 p-6" data-dashboard-card="detection-summary">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Akumulasi Deteksi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center justify-center">
                        <div class="relative w-44 h-44">
                            <svg viewBox="0 0 100 100" class="w-full h-full">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="18" />
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="18"
                                    stroke-dasharray="{{ $normalArc }} {{ $circumference }}"
                                    transform="rotate(-90 50 50)" />
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="18"
                                    stroke-dasharray="{{ $malwareArc }} {{ $circumference }}"
                                    stroke-dashoffset="-{{ $normalArc }}" transform="rotate(-90 50 50)" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalTraffic, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">total data</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 flex flex-col justify-center">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Normal</span>
                                <span class="font-semibold text-green-700">{{ number_format($normalPercentage, 2, ',', '.') }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($normalPercentage, 100) }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Malware</span>
                                <span class="font-semibold text-red-700">{{ number_format($malwarePercentage, 2, ',', '.') }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($malwarePercentage, 100) }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($canViewDashboardDetectionCard)
            {{-- Deteksi Malware Bar Chart --}}
            @php
                $barChartMax = max($totalTraffic, 1);
                // Calculate nice Y-axis steps
                $rawStep = $barChartMax / 5;
                $magnitude = pow(10, floor(log10(max($rawStep, 1))));
                $niceStep = ceil($rawStep / $magnitude) * $magnitude;
                $barChartCeil = $niceStep * 5;
                if ($barChartCeil < $barChartMax) {
                    $barChartCeil = $niceStep * 6;
                }
                $yAxisSteps = [];
                for ($i = 5; $i >= 0; $i--) {
                    $yAxisSteps[] = $niceStep * $i;
                }

                $totalBarHeight = $barChartCeil > 0 ? ($totalTraffic / $barChartCeil) * 100 : 0;
                $normalBarHeight = $barChartCeil > 0 ? ($normalTotal / $barChartCeil) * 100 : 0;
                $malwareBarHeight = $barChartCeil > 0 ? ($malwareTotal / $barChartCeil) * 100 : 0;
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 p-6" data-dashboard-card="detection-chart">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Deteksi Malware</h3>
                @if ($totalTraffic > 0)
                    <div class="flex h-56">
                        {{-- Y-Axis Labels --}}
                        <div class="flex flex-col justify-between pr-3 text-right flex-shrink-0 -mt-1.5 -mb-1.5">
                            @foreach ($yAxisSteps as $step)
                                <span class="text-xs text-gray-400 leading-none">{{ number_format($step, 0, ',', '.') }}</span>
                            @endforeach
                        </div>
                        {{-- Chart Area --}}
                        <div class="flex-1 flex flex-col">
                            <div class="flex-1 border-l border-b border-gray-200 relative">
                                {{-- Grid Lines --}}
                                @for ($i = 1; $i <= 4; $i++)
                                    <div class="absolute w-full border-t border-gray-100" style="top: {{ ($i / 5) * 100 }}%;"></div>
                                @endfor
                                <div class="absolute w-full border-t border-gray-100" style="top: 0;"></div>

                                {{-- Bars --}}
                                <div class="absolute inset-0 flex items-end justify-around px-2 gap-2 pb-0">
                                    {{-- Total Data Bar --}}
                                    <div class="flex-1 flex flex-col items-center justify-end h-full max-w-20">
                                        <div class="w-full rounded-t-md bg-blue-500 transition-all duration-700 ease-out relative group"
                                            style="height: {{ max($totalBarHeight, 1) }}%;">
                                            <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                                {{ number_format($totalTraffic, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Normal Bar --}}
                                    <div class="flex-1 flex flex-col items-center justify-end h-full max-w-20">
                                        <div class="w-full rounded-t-md bg-green-500 transition-all duration-700 ease-out relative group"
                                            style="height: {{ max($normalBarHeight, 1) }}%;">
                                            <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                                {{ number_format($normalTotal, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Malware Bar --}}
                                    <div class="flex-1 flex flex-col items-center justify-end h-full max-w-20">
                                        <div class="w-full rounded-t-md bg-red-500 transition-all duration-700 ease-out relative group"
                                            style="height: {{ max($malwareBarHeight, 1) }}%;">
                                            <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                                {{ number_format($malwareTotal, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- X-Axis Labels --}}
                            <div class="flex justify-around px-2 mt-2.5">
                                <span class="flex-1 text-center text-xs text-gray-600 font-medium max-w-20">Total<br>Data</span>
                                <span class="flex-1 text-center text-xs text-gray-600 font-medium max-w-20">Normal</span>
                                <span class="flex-1 text-center text-xs text-gray-600 font-medium max-w-20">Malware</span>
                            </div>
                        </div>
                    </div>
                    {{-- Legend --}}
                    <div class="flex items-center justify-center gap-4 mt-4 pt-3 border-t border-gray-100">
                        <span class="flex items-center gap-1.5 text-xs text-gray-600">
                            <span class="w-2.5 h-2.5 bg-blue-500 rounded-sm"></span> Total Data
                        </span>
                        <span class="flex items-center gap-1.5 text-xs text-gray-600">
                            <span class="w-2.5 h-2.5 bg-green-500 rounded-sm"></span> Normal
                        </span>
                        <span class="flex items-center gap-1.5 text-xs text-gray-600">
                            <span class="w-2.5 h-2.5 bg-red-500 rounded-sm"></span> Malware
                        </span>
                    </div>
                @else
                    <div class="h-56 flex items-center justify-center bg-gray-50 rounded-lg text-sm text-gray-500">
                        Data chart akan muncul setelah deteksi pertama.
                    </div>
                @endif
            </div>
        @endif

        @if ($canViewDashboardSuspiciousIpCard)
        <div class="bg-white rounded-lg border border-gray-200 p-6" data-dashboard-card="suspicious-ip-list">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Top IP Mencurigakan</h3>
            <div class="space-y-3">
                @forelse ($topSuspiciousIps as $ip)
                    @php
                        $location = $ip->location ?? ['label' => 'Lokasi tidak tersedia', 'source' => 'unavailable'];
                        $locationSource = $location['source'] ?? 'unavailable';
                        $canViewIpActivity = $canViewDashboardDetection;
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-gray-800 truncate">{{ $ip->source_ip }}</p>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded font-semibold">
                                    {{ number_format($ip->total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Rata-rata confidence {{ number_format(((float) $ip->avg_confidence) * 100, 2, ',', '.') }}%
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <span class="min-w-0 max-w-full truncate">Lokasi: {{ $location['label'] ?? 'Lokasi tidak tersedia' }}</span>
                            @if ($locationSource === 'api')
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded font-semibold">GeoIP</span>
                            @elseif ($locationSource === 'log')
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-semibold">Log</span>
                            @endif
                        </div>
                        @if ($canViewIpActivity)
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('dashboard.ip-activity', ['ip' => $ip->source_ip]) }}"
                                    class="inline-flex justify-center px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-xs font-semibold"
                                    aria-label="Lihat detail aktivitas IP {{ $ip->source_ip }}">
                                    Detail
                                </a>
                                <a href="{{ route('dashboard.ip-location', ['ip' => $ip->source_ip]) }}"
                                    class="inline-flex justify-center px-3 py-1.5 bg-blue-600 border border-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs font-semibold"
                                    aria-label="Lihat lokasi IP {{ $ip->source_ip }}">
                                    Lihat Lokasi
                                </a>
                            </div>
                        @endif
                        </div>
                @empty
                    <div class="p-8 bg-gray-50 rounded-lg text-center text-sm text-gray-500">
                        Belum ada IP dengan prediksi malware.
                    </div>
                @endforelse
            </div>
        </div>
        @endif


    </div>

    
</x-app-with-sidebar-layout>
