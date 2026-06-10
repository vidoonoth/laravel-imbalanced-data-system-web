<x-app-with-sidebar-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Deteksi Malware</h2>
                <p class="text-sm text-gray-500 mt-1">Upload file CSV log WatchGuard lalu hasilnya akan disimpan ke database.</p>
            </div>
            <div class="flex gap-2">
                @can('detection-history.view')
                    <a href="{{ route('detection.history') }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                        Riwayat
                    </a>
                @endcan
                <button type="button" onclick="clearResults()"
                    class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition text-sm font-semibold">
                    Bersihkan
                </button>
            </div>
        </div>
    </x-slot>

    <div class="w-full mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Upload Log Jaringan untuk Deteksi Malware</h3>

            <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" id="fileInput" name="file" accept=".csv"
                    style="display: none; visibility: hidden; position: absolute; left: -9999px;" />

                <div id="dropZone"
                    class="border-2 border-dashed border-blue-300 rounded-lg p-8 mb-6 cursor-pointer hover:bg-blue-50 transition">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-4 text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 9m0 0l-3-3m3 3l-3 3"></path>
                        </svg>
                        <p class="text-gray-800 font-semibold">Seret dan lepas file CSV di sini</p>
                        <p class="text-gray-500 text-sm">atau klik untuk pilih file</p>
                        <p class="text-gray-400 text-xs mt-2">Kolom CSV: update_time, sn, log, log_type</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <button type="button" onclick="startDetection()"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                        id="startBtn" disabled>
                        <span id="startBtnText">Mulai Deteksi</span>
                        <span id="startBtnSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Mendeteksi...
                        </span>
                    </button>
                    <button type="button" onclick="clearForm()"
                        class="w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Bersihkan Form
                    </button>
                </div>

                <div id="fileName" class="text-sm text-gray-600 mt-2"></div>
            </form>
        </div>
    </div>

    <div id="saveNotice" class="hidden mb-8 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-green-800" id="saveNoticeText">Hasil deteksi berhasil disimpan.</p>
            @can('detection-history.view')
                <a href="{{ route('detection.history') }}" id="saveNoticeLink"
                    class="inline-flex justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                    Lihat Riwayat
                </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="summaryStats" style="display: none;">
        <div class="bg-green-50 rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-green-600 text-sm font-semibold">Normal Terdeteksi</p>
            <p class="text-3xl font-bold text-green-700 mt-2" id="normalCount">0</p>
            <p class="text-xs text-green-600 mt-2" id="normalPercentage">0%</p>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-6 border-l-4 border-red-500">
            <p class="text-red-600 text-sm font-semibold">Malware Terdeteksi</p>
            <p class="text-3xl font-bold text-red-700 mt-2" id="attackCount">0</p>
            <p class="text-xs text-red-600 mt-2" id="attackPercentage">0%</p>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-blue-600 text-sm font-semibold">Total Dianalisis</p>
            <p class="text-3xl font-bold text-blue-700 mt-2" id="totalCount">0</p>
            <p class="text-xs text-blue-600 mt-2">record diproses</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" id="resultsSection" style="display: none;">
        <div class="p-6 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Hasil Deteksi</h3>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" id="searchInput" placeholder="Cari IP, policy, atau log..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <button type="button" onclick="exportResults()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                    Ekspor CSV
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="resultsTable">
                <thead class="bg-gray-50 border-b sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Indeks</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Waktu Log</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">SN</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Tipe Log</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Event</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Disposisi</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Prioritas</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Protokol</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">IP Sumber</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">IP Tujuan</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Port Sumber</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Port Tujuan</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Policy</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Geo Sumber</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Pckt Len</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">TTL</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Kepercayaan</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Prediksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" id="tableBody"></tbody>
            </table>
        </div>

        <div class="p-4 border-t text-center text-gray-600">
            <span id="resultCount">0 record ditampilkan</span>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-12 text-center" id="emptyState">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-gray-500">Upload file CSV untuk memulai deteksi malware</p>
    </div>

    <style>
        .drag-over {
            background-color: #f0f9ff !important;
            border-color: #3b82f6 !important;
        }

        #dropZone {
            pointer-events: auto;
            user-select: none;
        }

        #dropZone:hover {
            cursor: pointer;
        }
    </style>

    <script>
        let detectionResults = [];
        let selectedFile = null;

        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const searchInput = document.getElementById('searchInput');

            if (!dropZone || !fileInput) {
                return;
            }

            dropZone.addEventListener('click', function(event) {
                event.preventDefault();
                fileInput.click();
            });

            dropZone.addEventListener('dragover', function(event) {
                event.preventDefault();
                dropZone.classList.add('drag-over');
            });

            dropZone.addEventListener('dragleave', function(event) {
                event.preventDefault();
                dropZone.classList.remove('drag-over');
            });

            dropZone.addEventListener('drop', function(event) {
                event.preventDefault();
                dropZone.classList.remove('drag-over');

                if (event.dataTransfer.files.length > 0) {
                    handleFileSelect(event.dataTransfer.files[0]);
                }
            });

            fileInput.addEventListener('change', function(event) {
                if (event.target.files.length > 0) {
                    handleFileSelect(event.target.files[0]);
                }
            });

            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('#tableBody tr').forEach(function(row) {
                    const searchableText = `${row.textContent} ${row.dataset.search || ''}`.toLowerCase();
                    row.style.display = searchableText.includes(searchTerm) ? '' : 'none';
                });
            });
        });

        function handleFileSelect(file) {
            if (!file.name.toLowerCase().endsWith('.csv')) {
                alert('Pilih file CSV.');
                return;
            }

            selectedFile = file;
            document.getElementById('fileName').textContent =
                `Dipilih: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            document.getElementById('startBtn').disabled = false;
        }

        function clearForm() {
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('fileName').textContent = '';
            document.getElementById('startBtn').disabled = true;
        }

        async function startDetection() {
            if (!selectedFile) {
                alert('Pilih file terlebih dahulu.');
                return;
            }

            const formData = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            formData.append('file', selectedFile);
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }

            setLoading(true);

            try {
                const response = await fetch('{{ route('ml.predict.file') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json().catch(function() {
                    return {};
                });

                if (!response.ok) {
                    throw new Error(data.message || `Kesalahan HTTP: ${response.status}`);
                }

                if (data.status !== 'success') {
                    throw new Error(data.message || 'Kesalahan tidak diketahui.');
                }

                detectionResults = data.results || [];
                displayResults(data);
                showSaveNotice(data.scan);
            } catch (error) {
                hideSaveNotice();
                alert('Kesalahan saat deteksi:\n' + error.message);
            } finally {
                setLoading(false);
            }
        }

        function setLoading(isLoading) {
            document.getElementById('startBtn').disabled = isLoading;
            document.getElementById('startBtnText').classList.toggle('hidden', isLoading);
            document.getElementById('startBtnSpinner').classList.toggle('hidden', !isLoading);
        }

        function displayResults(data) {
            const summary = data.summary || {};

            document.getElementById('normalCount').textContent = summary.normal_count || 0;
            document.getElementById('normalPercentage').textContent = formatNumber(summary.normal_percentage, 2) + '%';
            document.getElementById('attackCount').textContent = summary.attack_count || 0;
            document.getElementById('attackPercentage').textContent = formatNumber(summary.attack_percentage, 2) + '%';
            document.getElementById('totalCount').textContent = summary.total_samples || 0;

            document.getElementById('summaryStats').style.display = 'grid';
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('resultsSection').style.display = 'block';

            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';

            detectionResults.forEach(function(result) {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50' + (Number(result.prediction) === 1 ? ' bg-red-50' : '');
                row.dataset.search = [
                    result.log,
                    result.source_interface,
                    result.destination_interface,
                    result.geo_dst,
                    result.action,
                ].join(' ');

                appendCell(row, result.index);
                appendCell(row, result.update_time, 'px-6 py-4 whitespace-nowrap');
                appendCell(row, result.sn, 'px-6 py-4 text-gray-600');
                appendCell(row, result.log_type);
                appendCell(row, result.event_name, 'px-6 py-4 font-semibold');
                appendCell(row, result.disposition);
                appendCell(row, formatNumber(result.priority, 0));
                appendCell(row, result.protocol, 'px-6 py-4 font-semibold');
                appendCell(row, result.source_ip, 'px-6 py-4 text-gray-600');
                appendCell(row, result.destination_ip, 'px-6 py-4 text-gray-600');
                appendCell(row, result.source_port);
                appendCell(row, result.destination_port);
                appendCell(row, result.policy, 'px-6 py-4 max-w-64 truncate', result.policy);
                appendCell(row, result.geo_src);
                appendCell(row, formatNumber(result.pckt_len, 0));
                appendCell(row, formatNumber(result.ttl, 0));
                appendCell(row, formatNumber((Number(result.confidence) || 0) * 100, 2) + '%', 'px-6 py-4 font-semibold');

                const predictionCell = document.createElement('td');
                predictionCell.className = 'px-6 py-4';
                const badge = document.createElement('span');
                badge.className = Number(result.prediction) === 1 ?
                    'px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full' :
                    'px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full';
                badge.textContent = Number(result.prediction) === 1 ? 'Malware' : 'Normal';
                predictionCell.appendChild(badge);
                row.appendChild(predictionCell);

                tableBody.appendChild(row);
            });

            document.getElementById('resultCount').textContent = `${detectionResults.length} record ditampilkan`;
        }

        function appendCell(row, value, className = 'px-6 py-4', title = null) {
            const cell = document.createElement('td');
            cell.className = className;
            cell.textContent = value === null || value === undefined || value === '' ? '-' : value;
            if (title) {
                cell.title = title;
            }
            row.appendChild(cell);
        }

        function showSaveNotice(scan) {
            const notice = document.getElementById('saveNotice');
            const noticeText = document.getElementById('saveNoticeText');
            const noticeLink = document.getElementById('saveNoticeLink');

            if (!notice || !scan) {
                hideSaveNotice();
                return;
            }

            noticeText.textContent = `Hasil deteksi file ${scan.filename || selectedFile?.name || ''} berhasil disimpan ke database.`;
            if (noticeLink) {
                noticeLink.href = scan.history_url || '{{ route('detection.history') }}';
                noticeLink.textContent = scan.history_url ? 'Lihat Detail' : 'Lihat Riwayat';
            }
            notice.classList.remove('hidden');
        }

        function hideSaveNotice() {
            document.getElementById('saveNotice')?.classList.add('hidden');
        }

        function clearResults() {
            detectionResults = [];
            document.getElementById('summaryStats').style.display = 'none';
            document.getElementById('resultsSection').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            document.getElementById('tableBody').innerHTML = '';
            hideSaveNotice();
            clearForm();
        }

        function exportResults() {
            if (detectionResults.length === 0) {
                alert('Tidak ada hasil untuk diekspor.');
                return;
            }

            const header = [
                'Index',
                'update_time',
                'sn',
                'log_type',
                'log',
                'event_name',
                'disposition',
                'priority',
                'protocol',
                'source_ip',
                'destination_ip',
                'source_port',
                'destination_port',
                'source_interface',
                'destination_interface',
                'policy',
                'pckt_len',
                'ttl',
                'sent_bytes',
                'rcvd_bytes',
                'geo_src',
                'geo_dst',
                'action',
                'Confidence',
                'Prediction',
            ];

            const rows = detectionResults.map(function(result) {
                return [
                    result.index,
                    result.update_time,
                    result.sn,
                    result.log_type,
                    result.log,
                    result.event_name,
                    result.disposition,
                    result.priority,
                    result.protocol,
                    result.source_ip,
                    result.destination_ip,
                    result.source_port,
                    result.destination_port,
                    result.source_interface,
                    result.destination_interface,
                    result.policy,
                    result.pckt_len,
                    result.ttl,
                    result.sent_bytes,
                    result.rcvd_bytes,
                    result.geo_src,
                    result.geo_dst,
                    result.action,
                    formatNumber((Number(result.confidence) || 0) * 100, 2),
                    result.prediction_label,
                ];
            });

            const csv = [header, ...rows].map(function(row) {
                return row.map(csvEscape).join(',');
            }).join('\n');

            const blob = new Blob([csv], {
                type: 'text/csv',
            });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `hasil-deteksi-${Date.now()}.csv`;
            document.body.appendChild(link);
            link.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(link);
        }

        function formatNumber(value, fractionDigits = 2) {
            const numericValue = Number.parseFloat(value);
            return Number.isFinite(numericValue) ? numericValue.toFixed(fractionDigits) : '-';
        }

        function csvEscape(value) {
            return `"${String(value ?? '').replace(/"/g, '""')}"`;
        }
    </script>
</x-app-with-sidebar-layout>
