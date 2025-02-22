<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class GhanaPostGPSService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ghana_post_gps.url');
        $this->apiKey = config('services.ghana_post_gps.key');
    }

    /**
     * Validate and get coordinates for a GhanaPostGPS address
     *
     * @param string $gpsCode
     * @return array
     */
    public function validateAndGetCoordinates(string $gpsCode): array
    {
        $cacheKey = "ghana_post_gps_{$gpsCode}";

        return Cache::remember($cacheKey, 3600, function () use ($gpsCode) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/validate", [
                'gps_code' => $gpsCode
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to validate GPS code');
            }

            $data = $response->json();

            return [
                'is_valid' => $data['valid'] ?? false,
                'latitude' => $data['coordinates']['lat'] ?? null,
                'longitude' => $data['coordinates']['lng'] ?? null,
                'area' => $data['area'] ?? null,
                'region' => $data['region'] ?? null
            ];
        });
    }

    /**
     * Convert coordinates to GhanaPostGPS address
     *
     * @param float $latitude
     * @param float $longitude
     * @return string|null
     */
    public function getGPSCodeFromCoordinates(float $latitude, float $longitude): ?string
    {
        $cacheKey = "ghana_post_gps_reverse_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/reverse", [
                'lat' => $latitude,
                'lng' => $longitude
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            return $data['gps_code'] ?? null;
        });
    }

    /**
     * Get Google Maps URL for a GhanaPostGPS address
     *
     * @param string $gpsCode
     * @return string|null
     */
    public function getGoogleMapsUrl(string $gpsCode): ?string
    {
        try {
            $coordinates = $this->validateAndGetCoordinates($gpsCode);
            if (!$coordinates['is_valid']) {
                return null;
            }

            return sprintf(
                'https://www.google.com/maps/search/?api=1&query=%f,%f',
                $coordinates['latitude'],
                $coordinates['longitude']
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
