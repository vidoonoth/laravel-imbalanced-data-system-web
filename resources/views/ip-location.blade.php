<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ Auth::user()->can('dashboard.view') ? route('dashboard') : route('profile.show') }}" class="text-gray-600 dark:text-gray-100 hover:text-gray-300 dark:hover:text-gray-100">Dashboard</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 dark:text-gray-100 text-[23px] font-semibold">Lokasi IP</span>
    </x-slot>

    @php
        $ipLocationLabel = $ipLocation['label'] ?? 'Lokasi tidak tersedia';
        $ipLocationSource = $ipLocation['source'] ?? 'unavailable';
        $ipLatitude = is_numeric($ipLocation['latitude'] ?? null) ? (float) $ipLocation['latitude'] : null;
        $ipLongitude = is_numeric($ipLocation['longitude'] ?? null) ? (float) $ipLocation['longitude'] : null;
        $hasMapCoordinates = $ipLatitude !== null && $ipLongitude !== null;
        $mapEmbedUrl = null;
        $mapViewUrl = null;
        $mapCoordinateLabel = '-';

        if ($hasMapCoordinates) {
            $latitude = number_format($ipLatitude, 6, '.', '');
            $longitude = number_format($ipLongitude, 6, '.', '');
            $mapDelta = 0.08;
            $bbox = implode(',', [
                number_format($ipLongitude - $mapDelta, 6, '.', ''),
                number_format($ipLatitude - $mapDelta, 6, '.', ''),
                number_format($ipLongitude + $mapDelta, 6, '.', ''),
                number_format($ipLatitude + $mapDelta, 6, '.', ''),
            ]);
            $mapEmbedUrl = "https://www.openstreetmap.org/export/embed.html?bbox={$bbox}&layer=mapnik&marker={$latitude},{$longitude}";
            $mapViewUrl = "https://www.openstreetmap.org/?mlat={$latitude}&mlon={$longitude}#map=10/{$latitude}/{$longitude}";
            $mapCoordinateLabel = "{$latitude}, {$longitude}";
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-300">Source IP</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-2 truncate" title="{{ $ipAddress }}">{{ $ipAddress }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">source IP dari hasil deteksi</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-300">Lokasi</p>
            <p class="text-lg font-bold text-gray-800 dark:text-gray-100 mt-2 truncate" title="{{ $ipLocationLabel }}">{{ $ipLocationLabel }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">Koordinat {{ $mapCoordinateLabel }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm text-gray-500 dark:text-gray-300">Lokasi Asal IP</p>
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mt-1 truncate" title="{{ $ipLocationLabel }}">{{ $ipLocationLabel }}</h3>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-300">
                    @if ($ipLocationSource === 'api')
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded font-semibold">data GeoIP</span>
                    @elseif ($ipLocationSource === 'log')
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded font-semibold">kode geo dari log</span>
                    @else
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded font-semibold">belum tersedia</span>
                    @endif
                    <span>Koordinat: {{ $mapCoordinateLabel }}</span>
                </div>
            </div>

            @if ($mapViewUrl)
                <a href="{{ $mapViewUrl }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex justify-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition text-xs font-semibold">
                    Buka OpenStreetMap
                </a>
            @endif
        </div>

        @if ($hasMapCoordinates)
            <div class="grid grid-cols-1 lg:grid-cols-3">
                <div class="lg:col-span-2 h-80 bg-gray-100 dark:bg-gray-700">
                    <iframe
                        title="Peta lokasi asal IP {{ $ipAddress }}"
                        src="{{ $mapEmbedUrl }}"
                        class="w-full h-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="p-5 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-700">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-300">Negara</dt>
                            <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">{{ $ipLocation['country'] ?? $ipLocation['country_code'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-300">Region/Kota</dt>
                            <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">
                                {{ collect([$ipLocation['region'] ?? null, $ipLocation['city'] ?? null])->filter()->implode(', ') ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-300">Latitude</dt>
                            <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($ipLatitude, 6, '.', '') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-300">Longitude</dt>
                            <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($ipLongitude, 6, '.', '') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @else
            <div class="p-6 bg-gray-50 dark:bg-gray-700 text-sm text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                Peta belum tersedia karena data koordinat tidak ditemukan untuk IP ini.
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-300">Negara</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">{{ $ipLocation['country'] ?? $ipLocation['country_code'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-300">Region/Kota</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">
                            {{ collect([$ipLocation['region'] ?? null, $ipLocation['city'] ?? null])->filter()->implode(', ') ?: '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-300">Latitude</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">-</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-300">Longitude</dt>
                        <dd class="font-semibold text-gray-800 dark:text-gray-100 mt-1">-</dd>
                    </div>
                </dl>
            </div>
        @endif
    </div>
</x-app-with-sidebar-layout>
