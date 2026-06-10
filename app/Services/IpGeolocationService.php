<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpGeolocationService
{
    public function lookup(?string $ipAddress, ?string $fallbackGeoCode = null): array
    {
        $ipAddress = trim((string) $ipAddress);
        $fallbackLocation = $this->fallbackLocation($fallbackGeoCode);

        if ($ipAddress === '' || ! $this->isPublicIp($ipAddress)) {
            return $fallbackLocation ?? $this->unavailableLocation();
        }

        $cacheKey = 'ip_geolocation:' . sha1($ipAddress);
        $cachedLocation = Cache::get($cacheKey);

        if (is_array($cachedLocation)) {
            if (($cachedLocation['source'] ?? null) === 'api' && ! $this->hasCoordinates($cachedLocation)) {
                $apiLocation = $this->lookupFromApi($ipAddress);

                if ($apiLocation !== null) {
                    Cache::put($cacheKey, $apiLocation, now()->addSeconds($this->cacheTtl()));

                    return $apiLocation;
                }
            }

            return $this->withCoordinateDefaults($cachedLocation);
        }

        $apiLocation = $this->lookupFromApi($ipAddress);

        if ($apiLocation !== null) {
            Cache::put($cacheKey, $apiLocation, now()->addSeconds($this->cacheTtl()));

            return $apiLocation;
        }

        return $fallbackLocation ?? $this->unavailableLocation();
    }

    private function isPublicIp(string $ipAddress): bool
    {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    private function lookupFromApi(string $ipAddress): ?array
    {
        $baseUrl = rtrim((string) config('services.ip_geolocation.base_url', 'https://ipwho.is'), '/');

        if ($baseUrl === '') {
            return null;
        }

        try {
            $response = Http::timeout(3)
                ->acceptJson()
                ->get($baseUrl . '/' . rawurlencode($ipAddress), [
                    'fields' => 'success,message,country,country_code,region,city,latitude,longitude',
                ]);

            if (! $response->ok()) {
                return null;
            }

            $payload = $response->json();

            if (! is_array($payload) || ($payload['success'] ?? true) === false) {
                return null;
            }

            return $this->formatLocation($payload, 'api');
        } catch (\Throwable $exception) {
            Log::notice('IP geolocation lookup failed.', [
                'ip' => $ipAddress,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function fallbackLocation(?string $geoCode): ?array
    {
        $countryCode = strtoupper($this->cleanValue($geoCode) ?? '');

        if ($countryCode === '' || in_array($countryCode, ['-', 'N/A', 'NA', 'NONE', 'NULL', 'UNKNOWN'], true)) {
            return null;
        }

        return [
            'label' => $countryCode,
            'country' => null,
            'country_code' => $countryCode,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'source' => 'log',
        ];
    }

    private function formatLocation(array $payload, string $source): ?array
    {
        $country = $this->cleanValue($payload['country'] ?? null);
        $countryCode = strtoupper($this->cleanValue($payload['country_code'] ?? null) ?? '');
        $region = $this->cleanValue($payload['region'] ?? null);
        $city = $this->cleanValue($payload['city'] ?? null);
        $latitude = $this->coordinateValue($payload['latitude'] ?? null, -90, 90);
        $longitude = $this->coordinateValue($payload['longitude'] ?? null, -180, 180);

        $labelParts = [];

        foreach ([$country, $region, $city] as $part) {
            if ($part !== null && ! in_array($part, $labelParts, true)) {
                $labelParts[] = $part;
            }
        }

        if ($labelParts === [] && $countryCode !== '') {
            $labelParts[] = $countryCode;
        }

        if ($labelParts === []) {
            return null;
        }

        return [
            'label' => implode(', ', $labelParts),
            'country' => $country,
            'country_code' => $countryCode !== '' ? $countryCode : null,
            'region' => $region,
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'source' => $source,
        ];
    }

    private function cleanValue($value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function hasCoordinates(array $location): bool
    {
        return $this->coordinateValue($location['latitude'] ?? null, -90, 90) !== null
            && $this->coordinateValue($location['longitude'] ?? null, -180, 180) !== null;
    }

    private function withCoordinateDefaults(array $location): array
    {
        $location['latitude'] = $this->coordinateValue($location['latitude'] ?? null, -90, 90);
        $location['longitude'] = $this->coordinateValue($location['longitude'] ?? null, -180, 180);

        return $location;
    }

    private function coordinateValue($value, float $min, float $max): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $coordinate = is_string($value) ? str_replace(',', '.', trim($value)) : $value;

        if (! is_numeric($coordinate)) {
            return null;
        }

        $coordinate = (float) $coordinate;

        return $coordinate >= $min && $coordinate <= $max ? $coordinate : null;
    }

    private function unavailableLocation(): array
    {
        return [
            'label' => 'Lokasi tidak tersedia',
            'country' => null,
            'country_code' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'source' => 'unavailable',
        ];
    }

    private function cacheTtl(): int
    {
        return max((int) config('services.ip_geolocation.cache_ttl', 60 * 60 * 24 * 30), 60);
    }
}
