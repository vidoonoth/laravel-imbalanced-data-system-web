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
                </div>
            @else
                <div class="p-8 bg-gray-50 rounded-lg text-center text-sm text-gray-500">
                    Sumber data akan muncul setelah import pertama.
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-800">Raw Data CSV</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">File</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Baris</th>
                        @foreach ($payloadColumns as $column)
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($datasets as $dataset)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-800 whitespace-nowrap">
                                {{ $dataset->import?->source_filename ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ number_format($dataset->row_number, 0, ',', '.') }}
                            </td>
                            @foreach ($payloadColumns as $column)
                                @php
                                    $value = ($dataset->payload ?? [])[$column] ?? '-';
                                    $displayValue = is_array($value) ? json_encode($value) : $value;
                                @endphp
                                <td class="px-4 py-3 text-gray-700 max-w-xs truncate">
                                    {{ $displayValue }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + $payloadColumns->count() }}" class="px-6 py-10 text-center text-gray-500">
                                Belum ada raw data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $datasets->links() }}
        </div>
    </div>
</x-app-with-sidebar-layout>
