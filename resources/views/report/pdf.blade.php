<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Deteksi Malware</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        @page {
            margin: 60px 40px 60px 40px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo {
            width: 70px;
            text-align: left;
            vertical-align: middle;
        }
        .header-logo img {
            height: 60px;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-title-main {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            margin: 0;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 9px;
            color: #4a5568;
            margin: 3px 0 0 0;
        }
        .report-title-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #2d3748;
            margin: 0;
            text-transform: uppercase;
        }
        .report-period {
            font-size: 10px;
            color: #718096;
            margin-top: 5px;
        }
        .meta-info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9px;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .meta-info-table td {
            padding: 2px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
            width: 33.3%;
        }
        .summary-label {
            font-size: 8px;
            color: #718096;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
        }
        .summary-subtext {
            font-size: 8px;
            color: #a0aec0;
            margin-top: 3px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a365d;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #edf2f7;
            color: #2d3748;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .data-table td {
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-geoip {
            background-color: #ebf8ff;
            color: #2b6cb0;
        }
        .footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .footer .page-number:after {
            content: counter(page);
        }
        .text-center {
            text-align: center !important;
        }
        .text-right {
            text-align: right !important;
        }
        .font-semibold {
            font-weight: bold;
        }
        .text-red {
            color: #c53030 !important;
        }
        .text-green {
            color: #2f855a !important;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo-polindra.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,' . $logoData;
        }
    @endphp

    <!-- Header Instansi -->
    <div class="header">
        <table class="header-table">
            <tr>
                @if($logoBase64)
                    <td class="header-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo Polindra">
                    </td>
                @endif
                <td class="header-text">
                    <h1 class="header-title-main">Unit Pelaksana Teknis (UPT) Teknologi Informasi & Komunikasi</h1>
                    <h2 class="header-title-main" style="font-size: 11px; margin-top: 2px;">Politeknik Negeri Indramayu</h2>
                    <p class="header-subtitle">Jl. Raya Lohbener Lama No.08, Lohbener, Kab. Indramayu, Jawa Barat 45252</p>
                    <p class="header-subtitle" style="margin-top: 1px;">Website: http://www.polindra.ac.id | Email: info@polindra.ac.id</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Judul Laporan -->
    <div class="report-title-container">
        <h2 class="report-title">Laporan Analisis Deteksi Malware</h2>
        <p class="report-period">
            Periode: 
            @if($dateFrom && $dateTo)
                {{ $dateFrom->timezone('Asia/Jakarta')->format('d/m/Y') }} s/d {{ $dateTo->timezone('Asia/Jakarta')->format('d/m/Y') }}
            @elseif($dateFrom)
                Mulai {{ $dateFrom->timezone('Asia/Jakarta')->format('d/m/Y') }}
            @elseif($dateTo)
                Hingga {{ $dateTo->timezone('Asia/Jakarta')->format('d/m/Y') }}
            @else
                Semua Waktu
            @endif
        </p>
    </div>

    <!-- Info Metadata -->
    <table class="meta-info-table">
        <tr>
            <td style="width: 50%;">Dicetak Oleh: <strong>{{ auth()->user()->name }} ({{ auth()->user()->email }})</strong></td>
            <td style="width: 50%; text-align: right;">Tanggal Cetak: <strong>{{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</strong></td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <table class="summary-table">
        <tr>
            <td class="summary-card" style="border-right: none; width: 33.3%;">
                <div class="summary-label">Total Log Traffic</div>
                <div class="summary-value">{{ number_format($totalTraffic, 0, ',', '.') }}</div>
                <div class="summary-subtext">Baris Log Dianalisis</div>
            </td>
            <td class="summary-card" style="border-right: none; width: 33.3%;">
                <div class="summary-label">Data Normal</div>
                <div class="summary-value text-green">{{ number_format($normalTotal, 0, ',', '.') }}</div>
                <div class="summary-subtext">{{ number_format($normalPercentage, 2, ',', '.') }}% dari total</div>
            </td>
            <td class="summary-card" style="width: 33.3%;">
                <div class="summary-label">Terdeteksi Malware</div>
                <div class="summary-value text-red">{{ number_format($malwareTotal, 0, ',', '.') }}</div>
                <div class="summary-subtext">{{ number_format($malwarePercentage, 2, ',', '.') }}% dari total</div>
            </td>
        </tr>
    </table>

    <!-- Statistik Deteksi Harian -->
    <div class="section-title">Statistik Deteksi Harian</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 25%;">Tanggal</th>
                <th style="width: 25%; text-align: right;">Total Log</th>
                <th style="width: 20%; text-align: right; color: #2f855a;">Normal</th>
                <th style="width: 25%; text-align: right; color: #c53030;">Malware (% Malware)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyStats as $index => $stat)
                @php
                    $total = (int) $stat->total_count;
                    $malware = (int) $stat->malware_count;
                    $malwarePct = $total > 0 ? ($malware / $total) * 100 : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ Carbon\Carbon::parse($stat->date)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                    <td class="text-right text-green font-semibold">{{ number_format((int) $stat->normal_count, 0, ',', '.') }}</td>
                    <td class="text-right text-red font-semibold">
                        {{ number_format($malware, 0, ',', '.') }} ({{ number_format($malwarePct, 2, ',', '.') }}%)
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 12px;">Belum ada data harian dalam rentang waktu ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Top IP Mencurigakan -->
    <div class="section-title">Top 10 IP Address Sumber Malware</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">IP Address</th>
                <th style="width: 35%;">Perkiraan Lokasi</th>
                <th style="width: 15%; text-align: center;">Jumlah Alert</th>
                <th style="width: 15%; text-align: right;">Avg Confidence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topSuspiciousIps as $index => $ip)
                @php
                    $location = $ip->location ?? ['label' => 'Lokasi tidak tersedia', 'source' => 'unavailable'];
                    $locationSource = $location['source'] ?? 'unavailable';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold text-red">{{ $ip->source_ip }}</td>
                    <td>
                        {{ $location['label'] }}
                        @if($locationSource === 'api')
                            <span class="badge badge-geoip">GeoIP</span>
                        @endif
                    </td>
                    <td class="text-center font-semibold">
                        <span style="color: #742a2a;">{{ number_format($ip->total, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-right font-semibold">
                        {{ number_format(((float) $ip->avg_confidence) * 100, 2, ',', '.') }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 12px;">Belum ada IP terdeteksi sebagai malware.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Riwayat Deteksi -->
    <div class="section-title">Riwayat Deteksi Log (Maks. 200 Deteksi Terbaru)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Waktu Deteksi</th>
                <th style="width: 20%;">Source IP</th>
                <th style="width: 20%;">Destination IP</th>
                <th style="width: 15%;">Protocol</th>
                <th style="width: 13%; text-align: center;">Prediction</th>
                <th style="width: 12%; text-align: right;">Confidence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentDetections as $record)
                <tr>
                    <td>
                        {{ $record->detected_at ? $record->detected_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') : $record->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="font-semibold">{{ $record->source_ip ?? '-' }}</td>
                    <td>{{ $record->destination_ip ?? '-' }}</td>
                    <td>{{ $record->protocol ?? '-' }}</td>
                    <td class="text-center font-semibold">
                        @if($record->prediction === 1)
                            <span style="color: #c53030;">MALWARE</span>
                        @else
                            <span style="color: #2f855a;">NORMAL</span>
                        @endif
                    </td>
                    <td class="text-right font-semibold">
                        {{ number_format(((float) $record->confidence) * 100, 2, ',', '.') }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 12px;">Belum ada riwayat deteksi yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini digenerate secara otomatis oleh Sistem Deteksi Malware UPA TIK Politeknik Negeri Indramayu. - Halaman <span class="page-number"></span>
    </div>
</body>
</html>
