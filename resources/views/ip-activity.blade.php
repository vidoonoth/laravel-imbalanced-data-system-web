<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ Auth::user()->can('dashboard.view') ? route('dashboard') : route('profile.show') }}" class="hover:text-gray-900">Dashboard</a>        
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 font-medium">Detail Aktivitas IP</span>
    </x-slot>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Detail Aktivitas IP</h2>
                <p class="text-sm text-gray-500 mt-1">Riwayat aktivitas source IP {{ $ipAddress }} dari hasil deteksi tersimpan.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard.ip-location', ['ip' => $ipAddress]) }}"
                    class="inline-flex justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                    Lihat Lokasi
                </a>
                <a href="{{ route('dashboard') }}"
                    class="inline-flex justify-center px-4 py-2 bg-gray-800 border border-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm font-semibold">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $firstSeen = $summary['first_seen']?->format('d/m/Y H:i:s') ?? '-';
        $lastSeen = $summary['last_seen']?->format('d/m/Y H:i:s') ?? '-';
        $avgAlertConfidence = $summary['avg_alert_confidence'] !== null
            ? number_format(((float) $summary['avg_alert_confidence']) * 100, 2, ',', '.') . '%'
            : '-';
        $frequentAccesses = $topEndpoints->isNotEmpty() ? $topEndpoints : $topDestinations;
        $frequentAccessTitle = $topEndpoints->isNotEmpty() ? 'Endpoint/URL Sering Diakses' : 'Tujuan Sering Diakses';
        $statusItems = $topResponseStatuses->isNotEmpty() ? $topResponseStatuses : $topDispositions;
        $statusTitle = $topResponseStatuses->isNotEmpty() ? 'Status Respons Dominan' : 'Disposisi Dominan';
        $suspiciousItems = $topSuspiciousEvents->isNotEmpty() ? $topSuspiciousEvents : $topSuspiciousActions;
        $suspiciousTitle = $topSuspiciousEvents->isNotEmpty() ? 'Event Mencurigakan' : 'Action Mencurigakan';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Jumlah Request</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($summary['total_activities'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">record aktivitas source IP</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Alert Malware</p>
            <p class="text-2xl font-bold text-red-700 mt-2">{{ number_format($summary['total_alerts'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">record dengan prediksi malware</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Rata-rata Confidence Alert</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ $avgAlertConfidence }}</p>
            <p class="text-xs text-gray-500 mt-1">dihitung dari alert malware</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Kemunculan IP</p>
            <p class="text-sm font-semibold text-gray-800 mt-2">{{ $firstSeen }}</p>
            <p class="text-xs text-gray-500 mt-1">terakhir {{ $lastSeen }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Event Dominan</h3>
            <div class="space-y-3">
                @forelse ($topEvents as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_activities'], 1)) * 100, 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-800 truncate">{{ $item['label'] }}</span>
                            <span class="text-gray-500">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 bg-gray-50 rounded-lg text-center text-sm text-gray-500">Event belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ $statusTitle }}</h3>
            <div class="space-y-3">
                @forelse ($statusItems as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_activities'], 1)) * 100, 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-800 truncate">{{ $item['label'] }}</span>
                            <span class="text-gray-500">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 bg-gray-50 rounded-lg text-center text-sm text-gray-500">Status belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ $frequentAccessTitle }}</h3>
            <div class="space-y-3">
                @forelse ($frequentAccesses as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_activities'], 1)) * 100, 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-800 truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                            <span class="text-gray-500">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 bg-gray-50 rounded-lg text-center text-sm text-gray-500">Tujuan akses belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Alert Terkait</h3>
                <p class="text-sm text-gray-500">Record malware terbaru dengan confidence tertinggi untuk IP ini.</p>
            </div>
            <span class="text-sm text-gray-500">{{ number_format($alerts->count(), 0, ',', '.') }} alert ditampilkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Waktu</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Event</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Disposisi</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Tujuan</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Action</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Confidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($alerts as $alert)
                        @php
                            $alertTime = $alert->update_time
                                ? $alert->update_time->format('d/m/Y H:i:s')
                                : $alert->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s');
                            $alertDestination = trim(($alert->destination_ip ?? '-') . ($alert->destination_port !== null ? ':' . $alert->destination_port : ''));
                        @endphp
                        <tr class="bg-red-50 hover:bg-red-100">
                            <td class="px-5 py-4 text-gray-800 whitespace-nowrap">{{ $alertTime ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $alert->event_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $alert->disposition ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $alertDestination !== '' ? $alertDestination : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $alert->action ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ $alert->confidence !== null ? number_format(((float) $alert->confidence) * 100, 2, ',', '.') . '%' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                Tidak ada alert malware untuk IP ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Riwayat Aktivitas</h3>
                <p class="text-sm text-gray-500">Semua record dari source IP {{ $ipAddress }}.</p>
            </div>
            <span class="text-sm text-gray-500">{{ number_format($activities->total(), 0, ',', '.') }} record</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Waktu</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Event</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Disposisi</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Source IP</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Destination</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Protocol</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Action</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Confidence</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Prediksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        @php
                            $activityTime = $activity->update_time
                                ? $activity->update_time->format('d/m/Y H:i:s')
                                : $activity->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s');
                            $destination = trim(($activity->destination_ip ?? '-') . ($activity->destination_port !== null ? ':' . $activity->destination_port : ''));
                        @endphp
                        <tr class="hover:bg-gray-50 {{ (int) $activity->prediction === 1 ? 'bg-red-50' : '' }}">
                            <td class="px-5 py-4 text-gray-800 whitespace-nowrap">{{ $activityTime ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $activity->event_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $activity->disposition ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $activity->source_ip ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $destination !== '' ? $destination : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $activity->protocol ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $activity->action ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ $activity->confidence !== null ? number_format(((float) $activity->confidence) * 100, 2, ',', '.') . '%' : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if ((int) $activity->prediction === 1)
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded font-semibold">Malware</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded font-semibold">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-10 text-center text-gray-500">
                                Tidak ada record aktivitas untuk IP ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-app-with-sidebar-layout>
