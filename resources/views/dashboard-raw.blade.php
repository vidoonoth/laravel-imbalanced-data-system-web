<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('dashboard.raw') }}" class="hover:text-gray-900">Dashboard Raw Data</a>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Dashboard Raw Data</h2>
                <p class="text-sm text-gray-500 mt-1">Ringkasan data mentah CSV yang diambil dari VPS tanpa penerapan deteksi malware.</p>
            </div>
        </div>
    </x-slot>

    @php
        $latestImportTime = $latestImport?->finished_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-';
        $latestImportDate = $latestImport?->finished_at?->timezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum ada import';
    @endphp

    @if ($totalRawRows === 0)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5">
            <p class="font-semibold text-blue-900">Belum ada raw data yang tersimpan.</p>
        </div>
    @endif

    <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr));">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Total Raw Data</p>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7h16M4 12h16M4 17h16"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalRawRows, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">baris CSV tersimpan</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">File Berhasil</p>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($completedImportCount, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">file completed</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">File Gagal</p>
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($failedImportCount, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">file failed</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-gray-600 text-sm font-medium">Import Terakhir</p>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $latestImportTime }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $latestImportDate }} WIB</p>
        </div>
    </div>

    <div class="grid gap-6 mb-6 items-start" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">File CSV Terbaru</h3>
            <div class="space-y-3">
                @forelse ($recentImports as $import)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-gray-800 truncate">{{ $import->source_filename ?? '-' }}</p>
                            <span class="px-2 py-1 text-xs rounded font-semibold {{ $import->status === 'completed' ? 'bg-green-100 text-green-700' : ($import->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $import->status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($import->rows_imported, 0, ',', '.') }} baris</p>
                        <p class="text-xs text-gray-500 mt-1 truncate">{{ $import->source_path }}</p>
                    </div>
                @empty
                    <div class="p-8 bg-gray-50 rounded-lg text-center text-sm text-gray-500">
                        Belum ada file CSV yang diimport.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Sumber Data</h3>
            @if ($latestImport)
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">Host</p>
                        <p class="text-gray-800 mt-1">{{ $latestImport->source_host ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">File Terakhir</p>
                        <p class="text-gray-800 mt-1 break-all">{{ $latestImport->source_filename ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">Path</p>
                        <p class="text-gray-800 mt-1 break-all">{{ $latestImport->source_path }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase">Jumlah Kolom CSV</p>
                        <p class="text-gray-800 mt-1">{{ $payloadColumns->count() }} kolom</p>
                    </div>
                </div>
            @else
                <div class="p-8 bg-gray-50 rounded-lg text-center text-sm text-gray-500">
                    Sumber data akan muncul setelah import pertama.
                </div>
            @endif
        </div>
    </div>

    {{-- Raw Data Table with Filters --}}
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Isi Raw Data CSV</h3>
                    <p class="text-xs text-gray-500 mt-1">Menampilkan seluruh isi file CSV yang sudah diimport dari VPS (semua file digabungkan).</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>{{ number_format($datasets->total(), 0, ',', '.') }} baris</span>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('dashboard.raw') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1 min-w-0">
                    <label for="filter-search" class="block text-xs font-semibold text-gray-600 mb-1">Cari Data</label>
                    <input
                        type="text"
                        id="filter-search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari berdasarkan IP, port, protocol..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div class="sm:w-56">
                    <label for="filter-file" class="block text-xs font-semibold text-gray-600 mb-1">Filter File CSV</label>
                    <select
                        id="filter-file"
                        name="file"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                        <option value="">Semua File</option>
                        @foreach ($completedImports as $importOption)
                            <option value="{{ $importOption->id }}" {{ ($filters['file'] ?? '') == $importOption->id ? 'selected' : '' }}>
                                {{ $importOption->source_filename }} ({{ number_format($importOption->rows_imported, 0, ',', '.') }} baris)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    @if (($filters['search'] ?? '') !== '' || ($filters['file'] ?? 0) > 0)
                        <a href="{{ route('dashboard.raw') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap sticky left-0 bg-gray-50 z-10">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">File Sumber</th>
                        @foreach ($payloadColumns as $column)
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($datasets as $index => $dataset)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs sticky left-0 bg-white z-10">
                                {{ $datasets->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded font-medium">
                                    {{ $dataset->import?->source_filename ?? '-' }}
                                </span>
                            </td>
                            @foreach ($payloadColumns as $column)
                                @php
                                    $value = ($dataset->payload ?? [])[$column] ?? null;
                                    if ($value === null || $value === '') {
                                        $displayValue = '-';
                                        $cellClass = 'text-gray-400';
                                    } else {
                                        $displayValue = is_array($value) ? json_encode($value) : (string) $value;
                                        $cellClass = 'text-gray-700';
                                    }
                                @endphp
                                <td class="px-4 py-3 {{ $cellClass }} max-w-xs truncate" title="{{ $displayValue }}">
                                    {{ $displayValue }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + $payloadColumns->count() }}" class="px-6 py-10 text-center text-gray-500">
                                @if (($filters['search'] ?? '') !== '' || ($filters['file'] ?? 0) > 0)
                                    Tidak ada data yang sesuai dengan filter.
                                @else
                                    Belum ada raw data.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs text-gray-500">
                Menampilkan {{ $datasets->firstItem() ?? 0 }} - {{ $datasets->lastItem() ?? 0 }} dari {{ number_format($datasets->total(), 0, ',', '.') }} baris
            </div>
            <div>
                {{ $datasets->links() }}
            </div>
        </div>
    </div>

</x-app-with-sidebar-layout>
