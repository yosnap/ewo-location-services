<?php
if (!defined('ABSPATH')) exit;

/**
 * Class for handling location coverage logic (session, API, helpers)
 */
class Ewo_Location_Coverage {
    /**
     * Save coordinates to PHP session
     */
    public static function save_coords_to_session($lat, $lng) {
        if (!session_id()) session_start();
        $_SESSION['ewo_location_coords'] = [
            'lat' => floatval($lat),
            'lng' => floatval($lng)
        ];
    }

    /**
     * Get coordinates from PHP session
     */
    public static function get_coords_from_session() {
        if (!session_id()) session_start();
        return isset($_SESSION['ewo_location_coords']) ? $_SESSION['ewo_location_coords'] : null;
    }

    /**
     * Get coverage status from API
     * Returns: Yes, Maybe, Not Yet, No (or null if not available)
     */
    public static function get_coverage_status() {
        // Get coordinates from session
        $coords = self::get_coords_from_session();
        if (!$coords || !isset($coords['lat'], $coords['lng'])) return null;
        $lat = $coords['lat'];
        $lng = $coords['lng'];
        // Get API config
        $env = get_option('ewo_env', 'dev');
        $api_key = get_option('ewo_api_key', '');
        $endpoint = ($env === 'prod')
            ? get_option('ewo_endpoint_getServiceabilityDetails_prod')
            : get_option('ewo_endpoint_getServiceabilityDetails_dev');
        if (!$api_key || !$endpoint) return null;
        // Prepare payload
        $payload = [
            'latitude' => (string)$lat,
            'longitude' => (string)$lng,
            'requesting_system' => 'website',
            'api-key' => $api_key
        ];
        $args = [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body' => json_encode($payload),
            'timeout' => 20
        ];
        $response = wp_remote_post($endpoint, $args);
        if (is_wp_error($response)) return null;
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (empty($data['success']) || empty($data['return-data']['serviceability-info']['coverage_confidence']['coverage_status'])) return null;
        // Normalize and return status (yes, maybe, not yet, no)
        $status = strtolower(trim($data['return-data']['serviceability-info']['coverage_confidence']['coverage_status']));
        // Map API status to allowed values
        if (in_array($status, ['yes', 'maybe', 'not yet', 'no'])) {
            return $status;
        }
        return null;
    }
} 