<?php

namespace App\Services;

use App\Models\Office;

class GeolocationService
{
    /**
     * Validasi apakah koordinat berada dalam geofence kantor
     */
    public function isWithinGeofence(Office $office, float $latitude, float $longitude): bool
    {
        if ($office->geofence_type === 'radius') {
            return $this->isWithinRadius(
                $office->latitude,
                $office->longitude,
                $office->radius_meters,
                $latitude,
                $longitude
            );
        }

        if ($office->geofence_type === 'polygon') {
            return $this->isWithinPolygon(
                $office->polygon_geojson,
                $latitude,
                $longitude
            );
        }

        return false;
    }

    /**
     * Cek apakah koordinat dalam radius (menggunakan Haversine formula)
     */
    private function isWithinRadius(
        float $centerLat,
        float $centerLng,
        int $radiusMeters,
        float $pointLat,
        float $pointLng
    ): bool {
        $distance = $this->calculateDistance($centerLat, $centerLng, $pointLat, $pointLng);
        return $distance <= $radiusMeters;
    }

    /**
     * Hitung jarak antara 2 koordinat (Haversine formula) dalam meter
     */
    public function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000; // dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Cek apakah koordinat dalam polygon (Ray Casting Algorithm)
     */
    private function isWithinPolygon(array $polygon, float $latitude, float $longitude): bool
    {
        // Polygon dalam format GeoJSON
        if (isset($polygon['coordinates'][0])) {
            $coordinates = $polygon['coordinates'][0];
        } else {
            return false;
        }

        $count = count($coordinates);
        $inside = false;

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $coordinates[$i][0];
            $yi = $coordinates[$i][1];
            $xj = $coordinates[$j][0];
            $yj = $coordinates[$j][1];

            $intersect = (($yi > $latitude) != ($yj > $latitude))
                && ($longitude < ($xj - $xi) * ($latitude - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Validasi akurasi GPS
     */
    public function isAccuracyAcceptable(float $accuracy, float $threshold = 50.0): bool
    {
        return $accuracy <= $threshold;
    }

    /**
     * Deteksi mock location (basic check)
     * Dalam implementasi production, bisa ditambahkan logika lebih kompleks
     */
    public function isMockLocation(array $deviceInfo): bool
    {
        // Check dari device info yang dikirim dari mobile
        return isset($deviceInfo['is_mock']) && $deviceInfo['is_mock'] === true;
    }
}
