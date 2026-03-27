<?php
/**
 * Reverse Geocoding Service
 * Converts coordinates to human-readable place names using Mapbox API
 */

namespace NokiaMaps\Service;

class ReverseGeocodingService
{
    private string $mapboxToken;
    private string $cacheDir;

    public function __construct(string $mapboxToken)
    {
        $this->mapboxToken = $mapboxToken;
        $this->cacheDir = __DIR__ . '/../cache/geocode';
        $this->ensureCacheDirectoryExists();
    }

    /**
     * Reverse geocode coordinates to get human-readable location name
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return string Human-readable location name or empty string on failure
     */
    public function reverseGeocode(float $lat, float $lon): string
    {
        if (empty($this->mapboxToken)) {
            return '';
        }

        $cached = $this->getCachedGeocode($lat, $lon);
        if ($cached !== null) {
            return $cached['place_name'] ?? '';
        }

        $apiResult = $this->fetchGeocodeFromApi($lat, $lon);
        if ($apiResult === null) {
            return '';
        }

        return $apiResult['place_name'] ?? '';
    }

    /**
     * Get cached geocode result if available and fresh
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|null Cached data or null if not available/expired
     */
    private function getCachedGeocode(float $lat, float $lon): ?array
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $cacheKey = md5("{$lon}_{$lat}");
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.json';

        if (!file_exists($cacheFile)) {
            return null;
        }

        // Check if cache is fresh (< 1 hour)
        if (filemtime($cacheFile) < time() - 3600) {
            return null;
        }

        $data = file_get_contents($cacheFile);
        if ($data === false) {
            return null;
        }

        $decoded = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    /**
     * Fetch geocode data from Mapbox Geocoding API
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|null API response data or null on failure
     */
    private function fetchGeocodeFromApi(float $lat, float $lon): ?array
    {
        $url = sprintf(
            'https://api.mapbox.com/geocoding/v5/mapbox.places/%s,%s.json?access_token=%s&limit=1',
            $lon,
            $lat,
            $this->mapboxToken,
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Shorter timeout for geocoding

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            error_log("Mapbox Geocoding API call failed with code: $httpCode");
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Failed to parse Mapbox Geocoding API response');
            return null;
        }

        if (empty($data['features'][0])) {
            error_log('No features found in geocoding response');
            return null;
        }

        $placeName = $data['features'][0]['place_name'] ?? '';

        // Cache the result
        $cacheDir = $this->cacheDir;
        $cacheKey = md5("{$lon}_{$lat}");
        $cacheFile = $cacheDir . '/' . $cacheKey . '.json';

        $cacheData = [
            'place_name' => $placeName,
            'timestamp' => time(),
        ];

        file_put_contents($cacheFile, json_encode($cacheData));

        return $cacheData;
    }

    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectoryExists(): void
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
}
