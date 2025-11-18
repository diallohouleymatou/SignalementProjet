<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $apiKey;
    protected string $provider;

    public function __construct()
    {
        $this->apiKey = config('services.geocoding.api_key', '');
        $this->provider = config('services.geocoding.provider', 'nominatim'); // nominatim (free) or google
    }

    /**
     * Geocode an address to coordinates
     */
    public function geocode(string $address): ?array
    {
        if ($this->provider === 'google') {
            return $this->geocodeGoogle($address);
        }

        return $this->geocodeNominatim($address);
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        if ($this->provider === 'google') {
            return $this->reverseGeocodeGoogle($latitude, $longitude);
        }

        return $this->reverseGeocodeNominatim($latitude, $longitude);
    }

    /**
     * Geocode using Nominatim (OpenStreetMap - Free)
     */
    protected function geocodeNominatim(string $address): ?array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $data = $response->json()[0];

                return [
                    'latitude' => (float) $data['lat'],
                    'longitude' => (float) $data['lon'],
                    'formatted_address' => $data['display_name'] ?? null,
                    'city' => $this->extractCity($data),
                    'country' => $data['address']['country'] ?? null,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reverse geocode using Nominatim
     */
    protected function reverseGeocodeNominatim(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'formatted_address' => $data['display_name'] ?? null,
                    'city' => $this->extractCity($data),
                    'country' => $data['address']['country'] ?? null,
                    'address' => $data['address'] ?? null,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Reverse geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Geocode using Google Maps API
     */
    protected function geocodeGoogle(string $address): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $this->apiKey,
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $result = $response->json('results')[0];
                $location = $result['geometry']['location'];

                return [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'formatted_address' => $result['formatted_address'],
                    'city' => $this->extractCityFromGoogle($result),
                    'country' => $this->extractCountryFromGoogle($result),
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Google geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reverse geocode using Google Maps API
     */
    protected function reverseGeocodeGoogle(float $latitude, float $longitude): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $this->apiKey,
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $result = $response->json('results')[0];

                return [
                    'formatted_address' => $result['formatted_address'],
                    'city' => $this->extractCityFromGoogle($result),
                    'country' => $this->extractCountryFromGoogle($result),
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Google reverse geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract city from Nominatim response
     */
    protected function extractCity(array $data): ?string
    {
        $address = $data['address'] ?? [];

        return $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? null;
    }

    /**
     * Extract city from Google response
     */
    protected function extractCityFromGoogle(array $result): ?string
    {
        foreach ($result['address_components'] as $component) {
            if (in_array('locality', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }

    /**
     * Extract country from Google response
     */
    protected function extractCountryFromGoogle(array $result): ?string
    {
        foreach ($result['address_components'] as $component) {
            if (in_array('country', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    public function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2,
        string $unit = 'km'
    ): float {
        $earthRadius = $unit === 'km' ? 6371 : 3959; // km or miles

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
