<?php
/**
 * Plantilla para mostrar el formulario de ubicación.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

// No permitir el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="ewo-location-services-container" class="ewo-location-services">
    <!-- ===================== Form Steps Progress ===================== -->
    <ol id="ewo-form-steps" class="ewo-form-steps ewo-form-steps-progress-bar">
        <li class="ewo-step active" data-step="1">1. Location</li>
        <li class="ewo-step" data-step="2">2. Service</li>
        <li class="ewo-step" data-step="3">3. Plan</li>
        <li class="ewo-step" data-step="4">4. Your Information</li>
        <li class="ewo-step" data-step="5">5. Addons</li>
        <li class="ewo-step" data-step="6">6. Confirmation</li>
    </ol>
    <div id="ewo-form-steps-progress" style="display:none;"></div>

    <!-- Formulario de ubicación -->
    <div id="ewo-location-form-container" class="ewo-section active">
        <h2><?php echo esc_html($atts['title']); ?></h2>
        
        <div class="ewo-form-group">
            <label for="ewo-address-input"><?php _e('Enter your address', 'ewo-location-services'); ?></label>
            <input type="text" id="ewo-address-input" placeholder="<?php _e('e.g. 123 Main St, City', 'ewo-location-services'); ?>" class="ewo-input">
            <button id="ewo-use-my-location" class="ewo-button ewo-button-secondary">
                <span class="dashicons dashicons-location"></span> <?php _e('Use my location', 'ewo-location-services'); ?>
            </button>
        </div>
        
        <!-- Contenedor del mapa -->
        <div id="ewo-map-container" style="height: 300px; margin-bottom: 20px;"></div>
        
        <!-- Campos ocultos para lat/lng -->
        <input type="hidden" id="ewo-latitude" name="latitude" value="">
        <input type="hidden" id="ewo-longitude" name="longitude" value="">
        
        <div class="ewo-form-actions">
            <button id="ewo-search-services" class="ewo-button ewo-button-primary">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
        </div>
    </div>
    
    <!-- ===================== Available Services Section ===================== -->
    <div id="ewo-available-services-section" class="ewo-section">
        <h2>Available Services</h2>
        <!-- Botones para volver al paso 1 -->
        <button id="ewo-change-location-btn" type="button" style="margin-bottom: 1rem;">Change location</button>
        <button id="ewo-back-to-location" class="ewo-button ewo-button-secondary" style="margin-bottom: 1rem;">
            <?php _e('Back', 'ewo-location-services'); ?>
        </button>
        <!-- Controls: filters, ordering, view toggle, columns, pagination -->
        <div id="ewo-services-controls"></div>
        <!-- Preloader -->
        <div id="ewo-loading-services" style="display:none; margin: 2rem 0; text-align:center;">
            <span class="ewo-spinner"></span> Loading services...
        </div>
        <!-- Services list -->
        <div id="ewo-services-list"></div>
        <!-- Pagination -->
        <div id="ewo-services-pagination"></div>
    </div>

    <!-- Service Template (used by JS) -->
    <template id="ewo-service-template">
        <div class="ewo-service-item">
            <div class="ewo-service-header">
                <h3 class="ewo-service-name"></h3>
                <div class="ewo-service-price"></div>
            </div>
            <div class="ewo-service-description"></div>
            <div class="ewo-service-action">
                <button class="ewo-button ewo-button-primary ewo-select-service" data-service-id="">
                    Select
                </button>
            </div>
        </div>
    </template>
    
    <!-- Resultados de servicios (inicialmente oculto) -->
    <div id="ewo-services-container" class="ewo-section">
        <h2><?php _e('Available Services', 'ewo-location-services'); ?></h2>
        <div class="ewo-services-list"></div>
        
        <div class="ewo-form-actions">
            <button id="ewo-back-to-location" class="ewo-button ewo-button-secondary">
                <?php _e('Back', 'ewo-location-services'); ?>
            </button>
        </div>
        
        <div class="ewo-loading" style="display:none;">
            <div class="ewo-spinner"></div>
            <p><?php _e('Loading available services...', 'ewo-location-services'); ?></p>
        </div>
        
        <div id="ewo-services-error" class="ewo-error">
            <p class="ewo-error-message"></p>
            <button class="ewo-button ewo-button-secondary ewo-retry-button">
                <?php _e('Try Again', 'ewo-location-services'); ?>
            </button>
        </div>
    </div>
    
    <!-- Nuevo paso: Selección de Plan (inicialmente oculto) -->
    <div id="ewo-plan-container" class="ewo-section">
        <h2><?php _e('Choose Your Plan', 'ewo-location-services'); ?></h2>
        <div id="ewo-plan-list"></div>
        <div class="ewo-form-actions">
            <button id="ewo-back-to-services" class="ewo-button ewo-button-secondary"><?php _e('Back', 'ewo-location-services'); ?></button>
            <button id="ewo-continue-to-user" class="ewo-button ewo-button-primary"><?php _e('Continue', 'ewo-location-services'); ?></button>
        </div>
    </div>
    
    <!-- Addons/Cross-sell (inicialmente oculto) -->
    <div id="ewo-addons-container" class="ewo-section">
        <h2><?php _e('Additional Options', 'ewo-location-services'); ?></h2>
        <p class="ewo-section-description"><?php _e('Enhance your service with these additional options:', 'ewo-location-services'); ?></p>
        
        <div id="ewo-addons-list" class="ewo-addons-list"></div>
        
        <template id="ewo-addon-template">
            <div class="ewo-addon-item">
                <div class="ewo-addon-checkbox">
                    <input type="checkbox" class="ewo-addon-select" name="addons[]" value="">
                </div>
                <div class="ewo-addon-content">
                    <h3 class="ewo-addon-name"></h3>
                    <div class="ewo-addon-price"></div>
                    <div class="ewo-addon-description"></div>
                </div>
            </div>
        </template>
        
        <div class="ewo-form-actions">
            <button id="ewo-back-to-user" class="ewo-button ewo-button-secondary">
                <?php _e('Back', 'ewo-location-services'); ?>
            </button>
            <button id="ewo-continue-to-confirmation" class="ewo-button ewo-button-primary">
                <?php _e('Continue', 'ewo-location-services'); ?>
            </button>
        </div>
    </div>
    
    <!-- Formulario de usuario (inicialmente oculto) -->
    <div id="ewo-user-container" class="ewo-section">
        <h2><?php _e('Your Information', 'ewo-location-services'); ?></h2>
        <p class="ewo-section-description"><?php _e('Please complete your information to finalize your service request:', 'ewo-location-services'); ?></p>
        
        <form id="ewo-user-form" class="ewo-form">
            <div class="ewo-form-group">
                <label for="ewo-user-first-name"><?php _e('First Name', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-user-first-name" name="first_name" class="ewo-input" required>
            </div>
            
            <div class="ewo-form-group">
                <label for="ewo-user-last-name"><?php _e('Last Name', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-user-last-name" name="last_name" class="ewo-input" required>
            </div>
            
            <div class="ewo-form-group">
                <label for="ewo-user-email"><?php _e('Email', 'ewo-location-services'); ?></label>
                <input type="email" id="ewo-user-email" name="email" class="ewo-input" required>
            </div>
            
            <?php if (!is_user_logged_in()): ?>
            <div class="ewo-form-group">
                <label for="ewo-user-username"><?php _e('Username', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-user-username" name="username" class="ewo-input" required>
            </div>
            <div class="ewo-form-group">
                <label for="ewo-user-password"><?php _e('Password', 'ewo-location-services'); ?></label>
                <input type="password" id="ewo-user-password" name="password" class="ewo-input" required>
            </div>
            <?php endif; ?>
            
            <div class="ewo-form-group">
                <label for="ewo-address-line-one"><?php _e('Address', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-address-line-one" name="address_line_one" class="ewo-input" required>
            </div>
            <div class="ewo-form-group">
                <label for="ewo-city"><?php _e('City', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-city" name="city" class="ewo-input" required>
            </div>
            <div class="ewo-form-group">
                <label for="ewo-state"><?php _e('State', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-state" name="state" class="ewo-input" required>
            </div>
            <div class="ewo-form-group">
                <label for="ewo-zip"><?php _e('ZIP', 'ewo-location-services'); ?></label>
                <input type="text" id="ewo-zip" name="zip" class="ewo-input" required>
            </div>
            
            <input type="hidden" id="ewo-selected-service" name="service_id" value="">
            <div id="ewo-selected-addons-container"></div>
            
            <div class="ewo-form-actions">
                <button id="ewo-back-to-services" class="ewo-button ewo-button-secondary">
                    <?php _e('Back', 'ewo-location-services'); ?>
                </button>
                <button type="submit" id="ewo-continue-to-addons" class="ewo-button ewo-button-primary">
                    <?php _e('Continue', 'ewo-location-services'); ?>
                </button>
            </div>
        </form>
        
        <!-- Mensaje de error -->
        <div id="ewo-user-error" class="ewo-error">
            <p class="ewo-error-message"></p>
        </div>
    </div>
    
    <!-- Confirmación (inicialmente oculta) -->
    <div id="ewo-confirmation-container" class="ewo-section">
        <div class="ewo-confirmation-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <h2><?php _e('Registration Successful!', 'ewo-location-services'); ?></h2>
            <p><?php _e('Thank you for registering. Your service request has been submitted successfully.', 'ewo-location-services'); ?></p>
            <div class="ewo-form-actions">
                <a href="<?php echo esc_url(home_url()); ?>" class="ewo-button ewo-button-primary">
                    <?php _e('Back to Home', 'ewo-location-services'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Debug Modal (inicialmente oculto) -->
    <div id="ewo-debug-modal" class="ewo-modal">
        <div class="ewo-modal-content">
            <span class="ewo-modal-close">&times;</span>
            <h3><?php _e('API Response Debug', 'ewo-location-services'); ?></h3>
            
            <div class="ewo-debug-tabs">
                <button class="ewo-debug-tab-button active" data-tab="formatted"><?php _e('Formatted View', 'ewo-location-services'); ?></button>
                <button class="ewo-debug-tab-button" data-tab="raw"><?php _e('Raw JSON', 'ewo-location-services'); ?></button>
                <button class="ewo-debug-tab-button" data-tab="structure"><?php _e('Structure Analysis', 'ewo-location-services'); ?></button>
            </div>
            
            <div class="ewo-debug-content">
                <div id="ewo-debug-tab-formatted" class="ewo-debug-tab active">
                    <h4><?php _e('Serviceable Locations', 'ewo-location-services'); ?></h4>
                    <div id="ewo-debug-formatted-services"></div>
                </div>
                <div id="ewo-debug-tab-raw" class="ewo-debug-tab">
                    <pre id="ewo-debug-json"></pre>
                </div>
                <div id="ewo-debug-tab-structure" class="ewo-debug-tab">
                    <h4><?php _e('Response Structure', 'ewo-location-services'); ?></h4>
                    <div id="ewo-debug-structure"></div>
                </div>
            </div>
            
            <div class="ewo-modal-footer">
                <button id="ewo-debug-copy" class="ewo-button ewo-button-secondary">
                    <?php _e('Copy to Clipboard', 'ewo-location-services'); ?>
                </button>
                <button id="ewo-debug-close" class="ewo-button ewo-button-primary">
                    <?php _e('Close', 'ewo-location-services'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Botón de depuración (visible solo para administradores) -->
    <?php if (current_user_can('administrator')): ?>
    <div id="ewo-debug-trigger-container">
        <button id="ewo-debug-trigger" class="ewo-button ewo-button-debug" title="<?php _e('Show API Response', 'ewo-location-services'); ?>">
            <span class="dashicons dashicons-code-standards"></span>
            <?php _e('Debug API', 'ewo-location-services'); ?>
        </button>
    </div>
    <?php endif; ?>
</div>
