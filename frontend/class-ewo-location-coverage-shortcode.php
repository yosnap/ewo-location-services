<?php
if (!defined('ABSPATH')) exit;
require_once dirname(__DIR__) . '/includes/class-ewo-location-coverage.php';

/**
 * Shortcode handler for automatic coverage status
 */
class Ewo_Location_Coverage_Shortcode {
    public function __construct() {
        add_shortcode('ewo_location_coverage_auto', [$this, 'render_shortcode']);
    }

    /**
     * Render the shortcode [ewo_location_coverage_auto]
     */
    public function render_shortcode($atts = [], $content = null) {
        $status = Ewo_Location_Coverage::get_coverage_status();
        $allowed = ['yes', 'maybe', 'not yet', 'no'];
        if ($status && in_array(strtolower($status), $allowed, true)) {
            return do_shortcode($content);
        }
        return '';
    }
}
// Instantiate
new Ewo_Location_Coverage_Shortcode(); 