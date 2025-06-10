<?php
if (!defined('ABSPATH')) exit;
require_once dirname(__FILE__) . '/class-ewo-location-coverage.php';

/**
 * Handles the location form submission and redirects based on coverage status
 */
class Ewo_Location_Handler {
    /**
     * Process the location form submit and redirect
     */
    public static function handle_location_submit() {
        // Check POST params
        $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
        $lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;
        if (!$lat || !$lng) {
            wp_redirect(home_url());
            exit;
        }
        // Save coordinates in session
        Ewo_Location_Coverage::save_coords_to_session($lat, $lng);
        // Get coverage status from API
        $status = Ewo_Location_Coverage::get_coverage_status();
        // Log temporal para depuración
        error_log('EWO Coverage status: ' . print_r($status, true));
        // Normalizar status
        $status = strtolower(trim($status));
        $status_key = '';
        switch ($status) {
            case 'yes': $status_key = 'ewo_page_coverage_yes'; break;
            case 'maybe': $status_key = 'ewo_page_coverage_maybe'; break;
            case 'not yet': $status_key = 'ewo_page_coverage_not_yet'; break;
            case 'no': $status_key = 'ewo_page_coverage_no'; break;
            default: $status_key = ''; break;
        }
        if ($status_key) {
            $page_id = get_option($status_key);
            if ($page_id) {
                $url = get_permalink($page_id);
                wp_redirect($url);
                exit;
            }
        }
        // Fallback: redirect to home
        wp_redirect(home_url());
        exit;
    }
}
// Register hooks for both logged-in and guest users
add_action('admin_post_nopriv_ewo_location_submit', ['Ewo_Location_Handler', 'handle_location_submit']);
add_action('admin_post_ewo_location_submit', ['Ewo_Location_Handler', 'handle_location_submit']); 