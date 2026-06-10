<x-app-with-sidebar-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Detail Riwayat Deteksi</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $scan->original_filename }}</p>
            </div>
            <a href="{{ route('detection.history') }}"
                class="inline-flex justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Data Log</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($scan->total_samples, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Normal</p>
            <p class="text-2xl font-bold text-green-700 mt-2">{{ number_format($scan->normal_count, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format((float) $scan->normal_percentage, 2, ',', '.') }}%</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Malware</p>
            <p class="text-2xl font-bold text-red-700 mt-2">{{ number_format($scan->attack_count, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format((float) $scan->attack_percentage, 2, ',', '.') }}%</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Waktu Scan</p>
            <p class="text-xl font-bold text-gray-800 mt-2">{{ $scan->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $scan->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }} WIB</p>
        </div>
    </div>

    @if ($scan->status === 'failed')
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-5">
            <p class="font-semibold text-red-800">Scan gagal diproses.</p>
            <p class="text-sm text-red-700 mt-1">{{ $scan->error_message ?? 'Tidak ada detail error.' }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Hasil Per Record</h3>
                <p class="text-sm text-gray-500">Data yang tersimpan dari respons model.</p>
            </div>
            <span class="text-sm text-gray-500">
                {{ number_format($results->total(), 0, ',', '.') }} record
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Index</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Waktu Log</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">SN</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Tipe Log</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Event</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Disposisi</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Prioritas</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Protocol</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Source IP</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Destination IP</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Source Port</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Destination Port</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Policy</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Pckt Len</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">TTL</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Geo Src</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Geo Dst</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Log</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Confidence</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Prediksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($results as $result)
                        <tr class="hover:bg-gray-50 {{ (int) $result->prediction === 1 ? 'bg-red-50' : '' }}">
                            <td class="px-5 py-4 text-gray-800">{{ $result->row_index }}</td>
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">{{ $result->update_time ? $result->update_time->format('d/m/Y H:i:s') : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->sn ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->log_type ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->event_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->disposition ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->priority ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->protocol ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->source_ip ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->destination_ip ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->source_port ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->destination_port ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">
                                <div class="max-w-64 truncate" title="{{ $result->policy ?? '' }}">
                                    {{ $result->policy ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->pckt_len !== null ? number_format($result->pckt_len, 0, ',', '.') : '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->ttl ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->geo_src ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $result->geo_dst ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-700">
                                <div class="max-w-96 truncate" title="{{ $result->log ?? '' }}">
                                    {{ $result->log ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ $result->confidence !== null ? number_format(((float) $result->confidence) * 100, 2, ',', '.') . '%' : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if ((int) $result->prediction === 1)
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded font-semibold">Malware</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded font-semibold">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="20" class="px-5 py-10 text-center text-gray-500">
                                Tidak ada record hasil deteksi pada scan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($results->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</x-app-with-sidebar-layout>
