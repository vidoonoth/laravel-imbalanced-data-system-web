<x-app-with-sidebar-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Riwayat Deteksi</h2>
                <p class="text-sm text-gray-500 mt-1">Daftar file log WatchGuard yang sudah diproses oleh model SSCL.</p>
            </div>
            <a href="{{ route('detection') }}"
                class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                Upload File
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Scan</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($summary['total_scans'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Scan Berhasil</p>
            <p class="text-2xl font-bold text-green-700 mt-2">{{ number_format($summary['successful_scans'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Data Log</p>
            <p class="text-2xl font-bold text-blue-700 mt-2">{{ number_format($summary['total_samples'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Malware</p>
            <p class="text-2xl font-bold text-red-700 mt-2">{{ number_format($summary['attack_count'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('detection.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari file atau status</label>
                <input type="text" id="q" name="q" value="{{ $filters['q'] }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="dataset.csv">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="success" @selected($filters['status'] === 'success')>Berhasil</option>
                    <option value="processing" @selected($filters['status'] === 'processing')>Diproses</option>
                    <option value="failed" @selected($filters['status'] === 'failed')>Gagal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-semibold">
                    Filter
                </button>
                <a href="{{ route('detection.history') }}"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Waktu</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">File</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Ukuran</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Total Log</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Normal</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Malware</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Malware %</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($scans as $scan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-800 whitespace-nowrap">
                                {{ $scan->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-64">
                                    <p class="font-semibold text-gray-800 truncate">{{ $scan->original_filename }}</p>
                                    <p class="text-xs text-gray-500">#{{ $scan->id }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                {{ number_format(($scan->file_size ?? 0) / 1024 / 1024, 2, ',', '.') }} MB
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ number_format($scan->total_samples, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-green-700 font-semibold">{{ number_format($scan->normal_count, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-red-700 font-semibold">{{ number_format($scan->attack_count, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ number_format((float) $scan->attack_percentage, 2, ',', '.') }}%</td>
                            <td class="px-5 py-4">
                                @if ($scan->status === 'success')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">Berhasil</span>
                                @elseif ($scan->status === 'failed')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Gagal</span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">Diproses</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('detection.history.show', $scan) }}"
                                    class="text-blue-600 hover:text-blue-800 font-semibold">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-gray-500">
                                Belum ada riwayat deteksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($scans->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $scans->links() }}
            </div>
        @endif
    </div>
</x-app-with-sidebar-layout>
