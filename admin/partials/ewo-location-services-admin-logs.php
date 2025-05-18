<?php
/**
 * Proporciona una vista de administración para los logs del plugin
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

// No permitir el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener el logger
$logger = new Ewo_Location_Services_Logger();

// Obtener el listado de archivos de log
$log_files = $logger->get_log_files();

// Determinar qué archivo mostrar
$current_log = isset($_GET['log']) && in_array($_GET['log'], $log_files) ? $_GET['log'] : (empty($log_files) ? '' : $log_files[0]);

// Comprobar si se ha limpiado recientemente el log
$log_cleared = isset($_GET['cleared']) && $_GET['cleared'] == '1';

// Obtener la ruta completa del archivo de log actual
$log_file_path = EWO_LOCATION_SERVICES_LOGS_DIR . $current_log;

// Obtener el contenido del log
$log_content = '';
if (!empty($current_log) && file_exists($log_file_path)) {
    $log_content = file_get_contents($log_file_path);
}

$opts = get_option('ewo_location_services_options');
$logging_enabled = isset($opts['logging_enabled']) ? $opts['logging_enabled'] : 'yes';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Logs', 'ewo-location-services'); ?></h1>
    <?php if (isset($_GET['logging_saved']) && $_GET['logging_saved'] == 1): ?>
    <div class="notice notice-success is-dismissible">
        <p>Logging settings saved successfully.</p>
    </div>
    <?php endif; ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:1.5em;">
        <?php wp_nonce_field('ewo_save_logging_settings', 'ewo_save_logging_settings_nonce'); ?>
        <input type="hidden" name="action" value="ewo_save_logging_settings">
        <label style="font-size:1.1em;font-weight:500;">
            <input type="checkbox" name="logging_enabled" value="yes" <?php checked($logging_enabled, 'yes'); ?>>
            <?php _e('Enable plugin logging (recommended for debugging)', 'ewo-location-services'); ?>
        </label>
        <button type="submit" class="button button-primary" style="margin-left:1em;">Save Changes</button>
    </form>
    <?php if ($logging_enabled !== 'yes'): ?>
    <div class="notice notice-warning" style="border-left:4px solid #d63638; background:#fff3f3;">
        <p><strong>Logging is currently disabled.</strong> No new log entries will be recorded until you enable logging in the plugin settings.</p>
    </div>
    <?php endif; ?>
    
    <?php if ($log_cleared == 1): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Log file deleted successfully.', 'ewo-location-services'); ?></p>
    </div>
    <?php elseif ($log_cleared == 2): ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e('Log file could not be deleted, but it was emptied successfully.', 'ewo-location-services'); ?></p>
    </div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e('Log file could not be deleted or emptied. Please check file permissions.', 'ewo-location-services'); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="ewo-admin-container">
        <div class="ewo-admin-main">
            <div class="ewo-log-controls">
                <?php if (!empty($log_files)): ?>
                <div class="ewo-log-file-selector">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="<?php echo esc_attr($this->plugin_name . '-logs'); ?>">
                        <select name="log" onchange="this.form.submit()">
                            <?php foreach ($log_files as $log_file): ?>
                            <option value="<?php echo esc_attr($log_file); ?>" <?php selected($current_log, $log_file); ?>>
                                <?php echo esc_html($log_file); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                
                <div class="ewo-log-actions">
                    <?php if (!empty($current_log)): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-logs&action=download_log&file=' . urlencode($current_log))); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-download"></span> <?php _e('Download Log', 'ewo-location-services'); ?>
                    </a>
                    
                    <form method="post" action="" style="display:inline;">
                        <?php wp_nonce_field('ewo_clear_log', 'ewo_clear_log_nonce'); ?>
                        <input type="hidden" name="action" value="clear_log">
                        <input type="hidden" name="log_file" value="<?php echo esc_attr($current_log); ?>">
                        <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear this log file?', 'ewo-location-services'); ?>')">
                            <span class="dashicons dashicons-trash"></span> <?php _e('Clear Log', 'ewo-location-services'); ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="notice notice-warning">
                    <p><?php _e('No log files found.', 'ewo-location-services'); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="ewo-log-viewer">
                <?php if (!empty($log_content)): ?>
                <pre class="ewo-log-content"><?php echo esc_html($log_content); ?></pre>
                <?php else: ?>
                <div class="notice notice-info">
                    <p><?php _e('Log file is empty or does not exist.', 'ewo-location-services'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="ewo-admin-sidebar">
            <div class="ewo-admin-box">
                <h3><?php _e('Log Information', 'ewo-location-services'); ?></h3>
                <?php if (!empty($current_log)): ?>
                <p><strong><?php _e('Current Log:', 'ewo-location-services'); ?></strong> <?php echo esc_html($current_log); ?></p>
                <p><strong><?php _e('File Size:', 'ewo-location-services'); ?></strong> <?php echo esc_html(size_format(filesize($log_file_path))); ?></p>
                <p><strong><?php _e('Last Modified:', 'ewo-location-services'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), filemtime($log_file_path))); ?></p>
                <?php else: ?>
                <p><?php _e('No log file selected.', 'ewo-location-services'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="ewo-admin-box">
                <h3><?php _e('Log Types', 'ewo-location-services'); ?></h3>
                <ul>
                    <li><strong>[INFO]</strong> - <?php _e('General information about plugin operations.', 'ewo-location-services'); ?></li>
                    <li><strong>[WARNING]</strong> - <?php _e('Important notices that aren\'t critical errors.', 'ewo-location-services'); ?></li>
                    <li><strong>[ERROR]</strong> - <?php _e('Critical errors that affect plugin functionality.', 'ewo-location-services'); ?></li>
                    <li><strong>[DEBUG]</strong> - <?php _e('Detailed information for troubleshooting.', 'ewo-location-services'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
