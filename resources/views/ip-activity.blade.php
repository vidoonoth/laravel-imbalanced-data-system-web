<x-app-with-sidebar-layout>

<style>
/* ===== TOOLTIP STYLES ===== */
.tip-wrap {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.tip-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #94a3b8;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    cursor: help;
    flex-shrink: 0;
    line-height: 1;
    transition: background 0.15s;
}
.tip-icon:hover {
    background: #475569;
}
/* tip-box is now fixed-positioned and controlled via JS */
.tip-box {
    display: none;
    position: fixed;
    background: #1e293b;
    color: #f1f5f9;
    font-size: 11.5px;
    font-weight: 400;
    line-height: 1.5;
    padding: 8px 11px;
    border-radius: 8px;
    white-space: normal;
    width: 220px;
    z-index: 99999;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    pointer-events: none;
}
.tip-box.tip-visible {
    display: block;
}
/* arrow – direction set by JS via class */
.tip-box::after {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
}
.tip-box.tip-above::after {
    top: 100%;
    border-top-color: #1e293b;
}
.tip-box.tip-below::after {
    bottom: 100%;
    border-bottom-color: #1e293b;
}
</style>
    <x-slot name="breadcrumbs">
        <a href="{{ Auth::user()->can('dashboard.view') ? route('dashboard') : route('profile.show') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Dashboard</a>        
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 dark:text-gray-100 text-[23px] font-semibold">Detail Aktivitas IP</span>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="tip-wrap">
                    Jumlah Request
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Total paket/koneksi yang dikirimkan oleh IP ini ke jaringan, sesuai data yang terekam di log sistem.</span>
                </span>
            </p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-2">{{ number_format($summary['total_activities'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">record aktivitas source IP</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="tip-wrap">
                    Alert Malware
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Jumlah record dari IP ini yang diklasifikasikan sebagai <strong>malware</strong> oleh model machine learning. Angka tinggi menandakan IP mencurigakan.</span>
                </span>
            </p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-2">{{ number_format($summary['total_alerts'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">record dengan prediksi malware</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="tip-wrap">
                    Kemunculan IP
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Pertama kali IP ini tercatat dalam log (pertama muncul) dan kapan terakhir kali terdeteksi aktif di jaringan.</span>
                </span>
            </p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mt-2">{{ $firstSeen }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">terakhir {{ $lastSeen }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">
                <span class="tip-wrap">
                    Event Dominan
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Jenis kejadian (event) jaringan yang paling sering muncul dari IP ini, misalnya <em>connection</em> (koneksi masuk/keluar) atau <em>dns</em> (permintaan DNS).</span>
                </span>
            </h3>
            <div class="space-y-3">
                @forelse ($topEvents as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_activities'], 1)) * 100, 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $item['label'] }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">Event belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">
                @if ($statusTitle === 'Disposisi Dominan')
                    <span class="tip-wrap">
                        {{ $statusTitle }}
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Keputusan firewall/sistem keamanan terhadap paket dari IP ini. Contoh: <strong>allowed</strong> = paket diizinkan lewat; <strong>blocked</strong> = paket diblokir; <strong>unknown</strong> = status tidak diketahui.</span>
                    </span>
                @else
                    <span class="tip-wrap">
                        {{ $statusTitle }}
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Kode respons HTTP yang paling sering dikembalikan server ke IP ini. Contoh: <strong>200</strong> = sukses; <strong>403</strong> = akses ditolak; <strong>404</strong> = halaman tidak ditemukan; <strong>500</strong> = error server.</span>
                    </span>
                @endif
            </h3>
            <div class="space-y-3">
                @forelse ($statusItems as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_activities'], 1)) * 100, 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $item['label'] }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">Status belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">
                @if ($frequentAccessTitle === 'Endpoint/URL Sering Diakses')
                    <span class="tip-wrap">
                        {{ $frequentAccessTitle }}
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Alamat halaman atau API (endpoint/URL) yang paling sering diminta oleh IP ini di server. Contoh: <em>/login</em>, <em>/api/data</em>.</span>
                    </span>
                @else
                    <span class="tip-wrap">
                        {{ $frequentAccessTitle }}
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Alamat IP atau host tujuan yang paling sering dihubungi oleh IP ini. Pola tujuan yang tidak biasa dapat mengindikasikan aktivitas mencurigakan.</span>
                    </span>
                @endif
            </h3>
            <div class="space-y-3">
                @forelse ($frequentAccesses as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_activities'], 1)) * 100, 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-800 dark:text-gray-100 truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">Tujuan akses belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    <span class="tip-wrap">
                        Alert Terkait
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Daftar record yang diprediksi sebagai <strong>malware</strong> oleh model ML, diurutkan dari confidence tertinggi. Ini menunjukkan aktivitas paling mencurigakan dari IP ini.</span>
                    </span>
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Record malware terbaru dengan confidence tertinggi untuk IP ini.</p>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($alerts->count(), 0, ',', '.') }} alert ditampilkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Waktu</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Event
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Jenis aktivitas jaringan yang tercatat, mis. <em>connection</em> (koneksi TCP/UDP), <em>dns</em> (query DNS), <em>http</em> (permintaan web).</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Disposisi
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Keputusan sistem keamanan terhadap paket ini: <strong>allowed</strong> = diizinkan, <strong>blocked</strong> = diblokir, <strong>unknown</strong> = tidak diketahui.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tujuan</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Action
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Tindakan spesifik yang diambil sistem, mis. <em>allow</em> (izinkan), <em>deny</em> (tolak), <em>drop</em> (abaikan tanpa respons).</span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($alerts as $alert)
                        @php
                            $alertTime = $alert->update_time
                                ? $alert->update_time->format('d/m/Y H:i:s')
                                : $alert->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s');
                            $alertDestination = trim(($alert->destination_ip ?? '-') . ($alert->destination_port !== null ? ':' . $alert->destination_port : ''));
                        @endphp
                        <tr class="bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30">
                            <td class="px-5 py-4 text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $alertTime ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $alert->event_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $alert->disposition ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $alertDestination !== '' ? $alertDestination : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $alert->action ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada alert malware untuk IP ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    <span class="tip-wrap">
                        Riwayat Aktivitas
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Seluruh log traffic jaringan dari IP ini, mencakup semua record baik yang diprediksi normal maupun malware. Baris merah = diprediksi malware.</span>
                    </span>
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Semua record dari source IP {{ $ipAddress }}.</p>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($activities->total(), 0, ',', '.') }} record</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Waktu</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Event
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Jenis aktivitas jaringan yang tercatat, mis. <em>connection</em>, <em>dns</em>, <em>http</em>.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Disposisi
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Keputusan sistem keamanan: <strong>allowed</strong> = paket diizinkan, <strong>blocked</strong> = paket diblokir, <strong>unknown</strong> = tidak diketahui.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Source IP
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Alamat IP pengirim paket — yaitu IP yang sedang dianalisis pada halaman ini.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Destination
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Alamat IP dan port tujuan yang dihubungi oleh Source IP. Format: <em>IP:port</em>, mis. <em>192.168.1.1:443</em>.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Protocol
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Protokol komunikasi yang digunakan: <strong>TCP</strong> = andal, ada konfirmasi paket; <strong>UDP</strong> = cepat tanpa konfirmasi; <strong>ICMP</strong> = ping/diagnostik jaringan.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Action
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Tindakan spesifik sistem: <em>allow</em> (izinkan), <em>deny</em> (tolak dengan respons), <em>drop</em> (abaikan tanpa respons).</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Prediksi
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Hasil klasifikasi model ML: <strong>Malware</strong> = terdeteksi sebagai traffic berbahaya; <strong>Normal</strong> = traffic biasa yang aman.</span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($activities as $activity)
                        @php
                            $activityTime = $activity->update_time
                                ? $activity->update_time->format('d/m/Y H:i:s')
                                : $activity->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s');
                            $destination = trim(($activity->destination_ip ?? '-') . ($activity->destination_port !== null ? ':' . $activity->destination_port : ''));
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ (int) $activity->prediction === 1 ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                            <td class="px-5 py-4 text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $activityTime ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->event_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->disposition ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->source_ip ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $destination !== '' ? $destination : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->protocol ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->action ?? '-' }}</td>
                            <td class="px-5 py-4">
                                @if ((int) $activity->prediction === 1)
                                    <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300 text-xs rounded font-semibold">Malware</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 text-xs rounded font-semibold">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada record aktivitas untuk IP ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-app-with-sidebar-layout>

<script>
(function () {
    // One shared tooltip box appended to <body> to escape all overflow:hidden ancestors
    var box = document.createElement('div');
    box.className = 'tip-box';
    document.body.appendChild(box);

    var GAP = 8;          // px gap between icon and tooltip
    var TIP_W = 220;      // must match CSS width
    var activeIcon = null;

    function show(icon) {
        var text = icon.nextElementSibling; // the inline .tip-box inside .tip-wrap
        if (!text) return;
        box.innerHTML = text.innerHTML;
        box.className = 'tip-box'; // reset classes

        // Make visible off-screen first to measure height
        box.style.visibility = 'hidden';
        box.style.display = 'block';
        var bh = box.offsetHeight;
        box.style.visibility = '';

        var r = icon.getBoundingClientRect();
        var iconCx = r.left + r.width / 2;
        var vw = window.innerWidth;
        var vh = window.innerHeight;

        // Decide vertical placement
        var spaceAbove = r.top;
        var spaceBelow = vh - r.bottom;
        var placeAbove = spaceAbove >= bh + GAP || spaceAbove >= spaceBelow;

        var top, left;
        if (placeAbove) {
            top = r.top - bh - GAP;
            box.classList.add('tip-above');
        } else {
            top = r.bottom + GAP;
            box.classList.add('tip-below');
        }

        // Centre horizontally on icon, clamp to viewport
        left = iconCx - TIP_W / 2;
        left = Math.max(8, Math.min(left, vw - TIP_W - 8));

        box.style.top  = top  + 'px';
        box.style.left = left + 'px';
        box.classList.add('tip-visible');
        activeIcon = icon;
    }

    function hide() {
        box.classList.remove('tip-visible', 'tip-above', 'tip-below');
        box.style.display = 'none';
        activeIcon = null;
    }

    // Event delegation on document so it works for dynamically added icons too
    document.addEventListener('mouseover', function (e) {
        var icon = e.target.closest('.tip-icon');
        if (icon && icon !== activeIcon) { hide(); show(icon); }
    });
    document.addEventListener('mouseout', function (e) {
        if (!e.target.closest('.tip-icon')) { hide(); }
    });
    // Also hide on scroll so it doesn't float in wrong position
    document.addEventListener('scroll', hide, true);
})();
</script>
