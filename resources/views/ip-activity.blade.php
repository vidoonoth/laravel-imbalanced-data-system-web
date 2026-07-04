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
            border-radius: 4px;
            white-space: normal;
            width: 320px;
            z-index: 99999;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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
        <a href="{{ Auth::user()->can('dashboard.view') ? route('dashboard') : route('profile.show') }}"
            class="text-gray-600 hover:text-gray-900 dark:text-gray-200 dark:hover:text-gray-100">Dashboard</a>
        <span class="text-gray-200">/</span>
        <span class="text-gray-900 dark:text-gray-100 text-[23px] font-semibold">Detail Aktivitas IP</span>
    </x-slot>

    @php
        $firstSeen = $summary['first_seen']?->format('d/m/Y H:i:s') ?? '-';
        $lastSeen = $summary['last_seen']?->format('d/m/Y H:i:s') ?? '-';
        $avgAlertConfidence =
            $summary['avg_alert_confidence'] !== null
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
            <p class="text-sm text-gray-500 dark:text-gray-200">
                <span class="tip-wrap">
                    Jumlah Request
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Ini menunjukkan berapa kali IP ini melakukan aktivitas di jaringan kampus. Setiap kali IP ini mengirim data atau mencoba akses sesuatu, itu dihitung sebagai 1 request. Jadi kalau angkanya besar, berarti IP ini sangat aktif dan sering berkomunikasi di jaringan kampus.</span>
                </span>
            </p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-2">
                {{ number_format($summary['total_activities'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-200 mt-1">record aktivitas source IP</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-200">
                <span class="tip-wrap">
                    Alert Malware
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Dari semua aktivitas IP ini, ini adalah jumlah aktivitas yang sistem kita deteksi sebagai berbahaya (malware). Makin banyak angka alert ini, makin berbahaya IP-nya karena banyak aktivitasnya yang mencurigakan.</span>
                </span>
            </p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-2">
                {{ number_format($summary['total_alerts'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-200 mt-1">record dengan prediksi malware</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-200">
                <span class="tip-wrap">
                    Kemunculan IP
                    <span class="tip-icon">?</span>
                    <span class="tip-box">Pertama kali IP ini tercatat dalam log (pertama muncul) dan kapan terakhir
                        kali terdeteksi aktif di jaringan.</span>
                </span>
            </p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mt-2">{{ $firstSeen }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-200 mt-1">terakhir {{ $lastSeen }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">
                <span class="tip-wrap">
                    Alasan IP Mencurigakan & Masuk Top
                    <span class="tip-icon">?</span>
                    <span class="tip-box">
                        <div
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <p class="text-[12px] text-gray-700 dark:text-gray-300 leading-relaxed">
                                <strong class="text-red-700 dark:text-red-400">Mengapa IP ini mencurigakan?</strong><br>
                                IP ini terdeteksi menghasilkan aktivitas yang diprediksi sebagai
                                <strong>malware</strong>.
                                Pola {{ $suspiciousTitle === 'Action Mencurigakan' ? 'action' : 'event' }} berikut
                                menunjukkan aktivitas yang perlu mendapat perhatian:
                            </p>

                            <ul class="mt-2 ml-4 text-xs text-gray-600 dark:text-gray-200 space-y-1">
                                <li>
                                    <strong>Allow</strong>: Aktivitas yang diizinkan oleh firewall, tetapi diprediksi
                                    sebagai
                                    ancaman oleh sistem. Kondisi ini perlu ditinjau karena lalu lintas yang berpotensi
                                    berbahaya
                                    tidak diblokir.
                                </li>

                                <li>
                                    <strong>Deny</strong>: Aktivitas yang diblokir oleh firewall. Hal ini menunjukkan
                                    adanya
                                    percobaan akses atau komunikasi yang dianggap tidak aman sehingga berhasil dicegah.
                                </li>
                            </ul>
                        </div>
                    </span>
                </span>
            </h3>



            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                {{ $suspiciousTitle }} Terdeteksi:</h4>
            <div class="space-y-3">
                @forelse ($suspiciousItems as $item)
                    @php
                        $width = min(($item['total'] / max($summary['total_alerts'], 1)) * 100, 100);
                        $actionLabel = strtolower($item['label']);
                        $isAllow = str_contains($actionLabel, 'allow');
                        $isDeny = str_contains($actionLabel, 'deny');
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                @if ($isAllow)
                                    <span
                                        class="px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs rounded font-semibold">KRITIKAL</span>
                                @elseif ($isDeny)
                                    <span
                                        class="px-2 py-0.5 bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 text-xs rounded font-semibold">PERCOBAAN</span>
                                @endif
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $item['label'] }}</span>
                            </div>
                            <span
                                class="text-gray-500 dark:text-gray-200 font-bold">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="{{ $isAllow ? 'bg-red-600' : 'bg-orange-500' }} h-2 rounded-full"
                                style="width: {{ $width }}%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-200 mt-1">
                            @if ($isAllow)
                                Ancaman berhasil menembus sistem keamanan
                            @elseif ($isDeny)
                                Upaya akses yang diblokir oleh firewall
                            @else
                                Aktivitas terdeteksi sebagai malware
                            @endif
                        </p>
                    </div>
                @empty
                    <p
                        class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg text-center text-sm text-gray-500 dark:text-gray-200">
                        Data aktivitas mencurigakan belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">
                @if ($frequentAccessTitle === 'Endpoint/URL Sering Diakses')
                    <span class="tip-wrap">
                        {{ $frequentAccessTitle }}
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Alamat halaman atau API (endpoint/URL) yang paling sering diminta oleh IP
                            ini di server. Contoh: <em>/login</em>, <em>/api/data</em>.</span>
                    </span>
                @else
                    <span class="tip-wrap">
                        {{ $frequentAccessTitle }}
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Daftar alamat IP tujuan yang paling sering dihubungi atau diakses oleh IP mencurigakan ini. Kalau IP ini sering banget akses ke satu tujuan tertentu, bisa jadi itu server komando untuk kontrol malware, atau target yang mau diserang. Pola akses yang nggak normal atau ke alamat yang aneh biasanya jadi tanda kalau IP ini lagi nyoba ngelakuin sesuatu yang berbahaya.</span>
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
                            <span class="font-semibold text-gray-800 dark:text-gray-100 truncate"
                                title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                            <span
                                class="text-gray-500 dark:text-gray-200">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $width }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p
                        class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg text-center text-sm text-gray-500 dark:text-gray-200">
                        Tujuan akses belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div
            class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    <span class="tip-wrap">
                        Riwayat Aktivitas
                        <span class="tip-icon">?</span>
                        <span class="tip-box">Seluruh log traffic jaringan dari IP ini, mencakup semua record baik yang
                            diprediksi normal maupun malware. Baris merah = diprediksi malware.</span>
                    </span>
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-200">Semua record dari source IP {{ $ipAddress }}.
                </p>
            </div>
            <span
                class="text-sm text-gray-500 dark:text-gray-200">{{ number_format($activities->total(), 0, ',', '.') }}
                record</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Waktu</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Source IP
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Alamat IP pengirim paket — yaitu IP yang sedang dianalisis pada
                                    halaman ini.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Destination
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Alamat IP dan port tujuan yang dihubungi oleh Source IP. Format:
                                    <em>IP:port</em>, mis. <em>192.168.1.1:443</em>.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Protocol
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Protokol itu kayak "bahasa" yang dipake komputer untuk ngobrol satu sama lain di jaringan. Ada beberapa jenis protokol dengan fungsi beda-beda: <strong>TCP</strong> adalah protokol yang paling aman dan teliti, setiap data yang dikirim pasti dikonfirmasi sampai atau nggak (makanya dipake buat browsing web, kirim email, download file). <strong>UDP</strong> lebih cepat tapi nggak ada konfirmasi, jadi cocok buat streaming video atau gaming online yang butuh kecepatan. <strong>ICMP</strong> dipake khusus untuk tes koneksi jaringan (kayak ping) atau kirim pesan error. Kalau IP mencurigakan banyak pake protokol tertentu dengan pola aneh, bisa jadi tanda serangan.</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Action
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Tindakan spesifik sistem: <em>allow</em> (izinkan), <em>deny</em>
                                    (tolak dengan respons), <em>drop</em> (abaikan tanpa respons).</span>
                            </span>
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            <span class="tip-wrap">
                                Prediksi
                                <span class="tip-icon">?</span>
                                <span class="tip-box">Hasil klasifikasi model ML: <strong>Malware</strong> = terdeteksi
                                    sebagai traffic berbahaya; <strong>Normal</strong> = traffic biasa yang aman.</span>
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
                            $destination = trim(
                                ($activity->destination_ip ?? '-') .
                                    ($activity->destination_port !== null ? ':' . $activity->destination_port : ''),
                            );
                        @endphp
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ (int) $activity->prediction === 1 ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                            <td class="px-5 py-4 text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                {{ $activityTime ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->source_ip ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">
                                {{ $destination !== '' ? $destination : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->protocol ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $activity->action ?? '-' }}</td>
                            <td class="px-5 py-4">
                                @if ((int) $activity->prediction === 1)
                                    <span
                                        class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300 text-xs rounded font-semibold">Malware</span>
                                @else
                                    <span
                                        class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 text-xs rounded font-semibold">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-200">
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
    (function() {
        // One shared tooltip box appended to <body> to escape all overflow:hidden ancestors
        var box = document.createElement('div');
        box.className = 'tip-box';
        document.body.appendChild(box);

        var GAP = 8; // px gap between icon and tooltip
        var TIP_W = 320; // must match CSS width
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

            box.style.top = top + 'px';
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
        document.addEventListener('mouseover', function(e) {
            var icon = e.target.closest('.tip-icon');
            if (icon && icon !== activeIcon) {
                hide();
                show(icon);
            }
        });
        document.addEventListener('mouseout', function(e) {
            if (!e.target.closest('.tip-icon')) {
                hide();
            }
        });
        // Also hide on scroll so it doesn't float in wrong position
        document.addEventListener('scroll', hide, true);
    })();
</script>
