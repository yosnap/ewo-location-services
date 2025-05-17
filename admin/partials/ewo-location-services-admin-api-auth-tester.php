<?php
/**
 * Herramienta avanzada de prueba de la API para diagnóstico de problemas de autenticación
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

// No permitir el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('administrator')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'ewo-location-services'));
}

// Obtener opciones
$options = get_option('ewo_location_services_options');
$environment = isset($options['api_environment']) ? $options['api_environment'] : 'development';
$api_key = isset($options['api_key']) ? $options['api_key'] : '';

// Determinar la URL del endpoint basada en el entorno seleccionado
$api_url = '';
if ($environment === 'development') {
    $api_url = isset($options['dev_get_serviceable_locations_by_latlng_url']) ? 
        $options['dev_get_serviceable_locations_by_latlng_url'] : '';
} else {
    $api_url = isset($options['prod_get_serviceable_locations_by_latlng_url']) ? 
        $options['prod_get_serviceable_locations_by_latlng_url'] : '';
}

// Manejar envío de formulario
$api_response = null;
$api_error = null;
$test_type = '';

if (isset($_POST['ewo_test_api'])) {
    if (!check_admin_referer('ewo_test_api_nonce', 'ewo_test_api_nonce')) {
        $api_error = __('Security check failed.', 'ewo-location-services');
    } else {
        $latitude = isset($_POST['test_latitude']) ? sanitize_text_field($_POST['test_latitude']) : '40.7128';
        $longitude = isset($_POST['test_longitude']) ? sanitize_text_field($_POST['test_longitude']) : '-74.0060';
        $test_type = isset($_POST['test_type']) ? sanitize_text_field($_POST['test_type']) : 'post';
        $api_key_format = isset($_POST['api_key_format']) ? sanitize_text_field($_POST['api_key_format']) : 'default';
        
        // Formatear la API key según la selección
        $formatted_api_key = $api_key;
        if ($api_key_format === 'no_hyphens') {
            $formatted_api_key = str_replace('-', '', $api_key);
        } else if ($api_key_format === 'uppercase') {
            $formatted_api_key = strtoupper($api_key);
        }
        
        if ($test_type === 'post_body') {
            // Probar con método POST - API key en el cuerpo
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'requesting_system' => 'website',
                    'api-key' => $formatted_api_key
                )),
                'method' => 'POST',
                'timeout' => 45,
                'data_format' => 'body'
            );
            
            $response = wp_remote_post($api_url, $args);
        } 
        else if ($test_type === 'post_header') {
            // Probar con método POST - API key en el encabezado
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'api-key' => $formatted_api_key
                ),
                'body' => json_encode(array(
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'requesting_system' => 'website'
                )),
                'method' => 'POST',
                'timeout' => 45,
                'data_format' => 'body'
            );
            
            $response = wp_remote_post($api_url, $args);
        }
        else if ($test_type === 'post_auth_header') {
            // Probar con método POST - API key en el encabezado Authorization
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $formatted_api_key
                ),
                'body' => json_encode(array(
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'requesting_system' => 'website'
                )),
                'method' => 'POST',
                'timeout' => 45,
                'data_format' => 'body'
            );
            
            $response = wp_remote_post($api_url, $args);
        }
        else if ($test_type === 'get_params') {
            // Probar con método GET - API key en parámetros URL
            $get_url = add_query_arg(array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => $formatted_api_key
            ), $api_url);
            
            $args = array(
                'method' => 'GET',
                'timeout' => 45
            );
            
            $response = wp_remote_get($get_url, $args);
        }
        else if ($test_type === 'get_header') {
            // Probar con método GET - API key en encabezados
            $get_url = add_query_arg(array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website'
            ), $api_url);
            
            $args = array(
                'headers' => array(
                    'api-key' => $formatted_api_key
                ),
                'method' => 'GET',
                'timeout' => 45
            );
            
            $response = wp_remote_get($get_url, $args);
        }
        else if ($test_type === 'get_auth_header') {
            // Probar con método GET - API key en encabezado Authorization
            $get_url = add_query_arg(array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website'
            ), $api_url);
            
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $formatted_api_key
                ),
                'method' => 'GET',
                'timeout' => 45
            );
            
            $response = wp_remote_get($get_url, $args);
        }
        
        // Procesar la respuesta
        if (is_wp_error($response)) {
            $api_error = $response->get_error_message();
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $headers = wp_remote_retrieve_headers($response);
            
            $api_response = array(
                'status_code' => $status_code,
                'headers' => $headers,
                'body' => $body,
                'body_decoded' => json_decode($body, true),
                'request_details' => array(
                    'method' => $test_type,
                    'api_key_format' => $api_key_format,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                )
            );
        }
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html__('API Authentication Tester', 'ewo-location-services'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Esta herramienta permite probar diferentes métodos de autenticación con la API para determinar el formato correcto.', 'ewo-location-services'); ?></p>
    </div>
    
    <?php if (!empty($api_url) && !empty($api_key)): ?>
        <div class="card" style="max-width: 800px; margin-bottom: 20px;">
            <h2><?php _e('Configuración actual de la API', 'ewo-location-services'); ?></h2>
            <p><strong><?php _e('Entorno:', 'ewo-location-services'); ?></strong> <?php echo esc_html($environment); ?></p>
            <p><strong><?php _e('API Key:', 'ewo-location-services'); ?></strong> <?php echo esc_html(substr($api_key, 0, 4) . '****' . substr($api_key, -4)); ?></p>
            <p><strong><?php _e('API URL:', 'ewo-location-services'); ?></strong> <?php echo esc_html($api_url); ?></p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('ewo_test_api_nonce', 'ewo_test_api_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="test_latitude"><?php _e('Latitud', 'ewo-location-services'); ?></label></th>
                    <td>
                        <input name="test_latitude" type="text" id="test_latitude" value="40.740117894418454" class="regular-text">
                        <p class="description"><?php _e('Ejemplo: 40.740117894418454', 'ewo-location-services'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="test_longitude"><?php _e('Longitud', 'ewo-location-services'); ?></label></th>
                    <td>
                        <input name="test_longitude" type="text" id="test_longitude" value="-74.23119877930732" class="regular-text">
                        <p class="description"><?php _e('Ejemplo: -74.23119877930732', 'ewo-location-services'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Método de prueba', 'ewo-location-services'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Método de prueba', 'ewo-location-services'); ?></legend>
                            
                            <label>
                                <input type="radio" name="test_type" value="post_body" <?php checked($test_type, 'post_body'); ?> <?php echo empty($test_type) ? 'checked' : ''; ?>>
                                <?php _e('POST con API key en el cuerpo', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="test_type" value="post_header" <?php checked($test_type, 'post_header'); ?>>
                                <?php _e('POST con API key en el encabezado', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="test_type" value="post_auth_header" <?php checked($test_type, 'post_auth_header'); ?>>
                                <?php _e('POST con API key en encabezado Authorization', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="test_type" value="get_params" <?php checked($test_type, 'get_params'); ?>>
                                <?php _e('GET con API key en parámetros URL', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="test_type" value="get_header" <?php checked($test_type, 'get_header'); ?>>
                                <?php _e('GET con API key en encabezado', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="test_type" value="get_auth_header" <?php checked($test_type, 'get_auth_header'); ?>>
                                <?php _e('GET con API key en encabezado Authorization', 'ewo-location-services'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Formato de API key', 'ewo-location-services'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Formato de API key', 'ewo-location-services'); ?></legend>
                            
                            <label>
                                <input type="radio" name="api_key_format" value="default" <?php checked(empty($api_key_format) || $api_key_format === 'default'); ?>>
                                <?php _e('Predeterminado (tal como está configurada)', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="api_key_format" value="no_hyphens" <?php checked($api_key_format, 'no_hyphens'); ?>>
                                <?php _e('Sin guiones', 'ewo-location-services'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="api_key_format" value="uppercase" <?php checked($api_key_format, 'uppercase'); ?>>
                                <?php _e('Todo en mayúsculas', 'ewo-location-services'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="ewo_test_api" class="button button-primary" value="<?php _e('Probar conexión API', 'ewo-location-services'); ?>">
            </p>
        </form>
        
        <?php if ($api_error): ?>
            <div class="notice notice-error">
                <p><strong><?php _e('Error:', 'ewo-location-services'); ?></strong> <?php echo esc_html($api_error); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($api_response): ?>
            <div class="card" style="max-width: 1200px; margin-top: 20px;">
                <h2><?php _e('Respuesta de la API', 'ewo-location-services'); ?></h2>
                
                <h3><?php _e('Código de estado:', 'ewo-location-services'); ?> <?php echo esc_html($api_response['status_code']); ?></h3>
                
                <?php if ($api_response['status_code'] == 200): ?>
                    <div class="notice notice-success inline">
                        <p><?php _e('¡Éxito! La API respondió con un código 200 OK.', 'ewo-location-services'); ?></p>
                    </div>
                <?php elseif ($api_response['status_code'] == 404): ?>
                    <div class="notice notice-warning inline">
                        <p><?php _e('La API respondió con un código 404 Not Found. Esto puede significar que la URL del endpoint es incorrecta, o que no hay servicios disponibles en la ubicación especificada.', 'ewo-location-services'); ?></p>
                    </div>
                <?php elseif ($api_response['status_code'] == 401 || $api_response['status_code'] == 403): ?>
                    <div class="notice notice-error inline">
                        <p><?php _e('La API respondió con un código de error de autenticación. Probablemente la API key es inválida o está en un formato incorrecto.', 'ewo-location-services'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="notice notice-warning inline">
                        <p><?php _e('La API respondió con un código no estándar. Revise los detalles de la respuesta.', 'ewo-location-services'); ?></p>
                    </div>
                <?php endif; ?>
                
                <h3><?php _e('Encabezados de respuesta:', 'ewo-location-services'); ?></h3>
                <pre style="background: #f6f7f7; padding: 15px; overflow: auto; max-height: 200px;"><?php print_r($api_response['headers']); ?></pre>
                
                <h3><?php _e('Cuerpo de respuesta (Raw):', 'ewo-location-services'); ?></h3>
                <pre style="background: #f6f7f7; padding: 15px; overflow: auto; max-height: 200px;"><?php echo esc_html($api_response['body']); ?></pre>
                
                <h3><?php _e('Cuerpo de respuesta (Decodificado):', 'ewo-location-services'); ?></h3>
                <pre style="background: #f6f7f7; padding: 15px; overflow: auto; max-height: 300px;"><?php print_r($api_response['body_decoded']); ?></pre>
                
                <h3><?php _e('Recomendaciones:', 'ewo-location-services'); ?></h3>
                <div style="background: #f6f7f7; padding: 15px; margin-bottom: 20px;">
                    <?php
                    // Analizar la respuesta para determinar recomendaciones
                    $status_code = $api_response['status_code'];
                    $body_decoded = $api_response['body_decoded'];
                    $recommendations = array();
                    
                    if ($status_code == 200) {
                        echo '<p class="success-message" style="color: green; font-weight: bold;">';
                        _e('✅ ¡Autenticación exitosa! Este método funciona correctamente.', 'ewo-location-services');
                        echo '</p>';
                        
                        // Detalles de la configuración exitosa
                        echo '<div class="successful-config" style="margin-top: 15px; border: 1px solid #ccd0d4; padding: 15px; background: #f0f6fc;">';
                        echo '<h4>' . __('Configuración correcta a implementar:', 'ewo-location-services') . '</h4>';
                        echo '<ul>';
                        echo '<li><strong>' . __('Método HTTP:', 'ewo-location-services') . '</strong> ' . 
                            ($api_response['request_details']['method'] == 'post_body' || 
                             $api_response['request_details']['method'] == 'post_header' || 
                             $api_response['request_details']['method'] == 'post_auth_header' ? 'POST' : 'GET') . '</li>';
                             
                        if ($api_response['request_details']['method'] == 'post_body') {
                            echo '<li><strong>' . __('Ubicación de API key:', 'ewo-location-services') . '</strong> ' . 
                                __('En el cuerpo de la solicitud', 'ewo-location-services') . '</li>';
                        } elseif ($api_response['request_details']['method'] == 'post_header' || $api_response['request_details']['method'] == 'get_header') {
                            echo '<li><strong>' . __('Ubicación de API key:', 'ewo-location-services') . '</strong> ' . 
                                __('En el encabezado api-key', 'ewo-location-services') . '</li>';
                        } elseif ($api_response['request_details']['method'] == 'post_auth_header' || $api_response['request_details']['method'] == 'get_auth_header') {
                            echo '<li><strong>' . __('Ubicación de API key:', 'ewo-location-services') . '</strong> ' . 
                                __('En el encabezado Authorization', 'ewo-location-services') . '</li>';
                        } elseif ($api_response['request_details']['method'] == 'get_params') {
                            echo '<li><strong>' . __('Ubicación de API key:', 'ewo-location-services') . '</strong> ' . 
                                __('En los parámetros URL', 'ewo-location-services') . '</li>';
                        }
                        
                        echo '<li><strong>' . __('Formato de API key:', 'ewo-location-services') . '</strong> ' . 
                            ($api_response['request_details']['api_key_format'] == 'default' ? __('Formato original', 'ewo-location-services') : 
                             ($api_response['request_details']['api_key_format'] == 'no_hyphens' ? __('Sin guiones', 'ewo-location-services') : 
                              __('Todo en mayúsculas', 'ewo-location-services'))) . '</li>';
                        echo '</ul>';
                        
                        // Código PHP de ejemplo
                        echo '<h4>' . __('Código para implementar en el plugin:', 'ewo-location-services') . '</h4>';
                        echo '<pre>';
                        if ($api_response['request_details']['method'] == 'post_body') {
                            echo '$args = array(
    \'headers\' => array(
        \'Content-Type\' => \'application/json\'
    ),
    \'body\' => json_encode(array(
        \'latitude\' => $latitude,
        \'longitude\' => $longitude,
        \'requesting_system\' => \'website\',
        \'api-key\' => $api_key  // API key en el cuerpo
    )),
    \'method\' => \'POST\',
    \'timeout\' => 45,
    \'data_format\' => \'body\'
);

$response = wp_remote_post($api_url, $args);';
                        } 
                        elseif ($api_response['request_details']['method'] == 'post_header') {
                            echo '$args = array(
    \'headers\' => array(
        \'Content-Type\' => \'application/json\',
        \'api-key\' => $api_key  // API key en el encabezado
    ),
    \'body\' => json_encode(array(
        \'latitude\' => $latitude,
        \'longitude\' => $longitude,
        \'requesting_system\' => \'website\'
    )),
    \'method\' => \'POST\',
    \'timeout\' => 45,
    \'data_format\' => \'body\'
);

$response = wp_remote_post($api_url, $args);';
                        }
                        elseif ($api_response['request_details']['method'] == 'post_auth_header') {
                            echo '$args = array(
    \'headers\' => array(
        \'Content-Type\' => \'application/json\',
        \'Authorization\' => \'Bearer \' . $api_key  // API key en el encabezado Authorization
    ),
    \'body\' => json_encode(array(
        \'latitude\' => $latitude,
        \'longitude\' => $longitude,
        \'requesting_system\' => \'website\'
    )),
    \'method\' => \'POST\',
    \'timeout\' => 45,
    \'data_format\' => \'body\'
);

$response = wp_remote_post($api_url, $args);';
                        }
                        elseif ($api_response['request_details']['method'] == 'get_params') {
                            echo '// Crear URL con parámetros para GET
$get_url = add_query_arg(array(
    \'latitude\' => $latitude,
    \'longitude\' => $longitude,
    \'requesting_system\' => \'website\',
    \'api-key\' => $api_key  // API key en los parámetros URL
), $api_url);

$args = array(
    \'method\' => \'GET\',
    \'timeout\' => 45
);

$response = wp_remote_get($get_url, $args);';
                        }
                        elseif ($api_response['request_details']['method'] == 'get_header') {
                            echo '// Crear URL con parámetros para GET
$get_url = add_query_arg(array(
    \'latitude\' => $latitude,
    \'longitude\' => $longitude,
    \'requesting_system\' => \'website\'
), $api_url);

$args = array(
    \'headers\' => array(
        \'api-key\' => $api_key  // API key en el encabezado
    ),
    \'method\' => \'GET\',
    \'timeout\' => 45
);

$response = wp_remote_get($get_url, $args);';
                        }
                        elseif ($api_response['request_details']['method'] == 'get_auth_header') {
                            echo '// Crear URL con parámetros para GET
$get_url = add_query_arg(array(
    \'latitude\' => $latitude,
    \'longitude\' => $longitude,
    \'requesting_system\' => \'website\'
), $api_url);

$args = array(
    \'headers\' => array(
        \'Authorization\' => \'Bearer \' . $api_key  // API key en el encabezado Authorization
    ),
    \'method\' => \'GET\',
    \'timeout\' => 45
);

$response = wp_remote_get($get_url, $args);';
                        }
                        echo '</pre>';
                        echo '</div>';
                    }
                    else if ($status_code == 404) {
                        if (isset($body_decoded['error']) && 
                            (stripos($body_decoded['error'], 'no locations') !== false || 
                             stripos($body_decoded['error'], 'no services') !== false)) {
                            echo '<p class="warning-message" style="color: orange; font-weight: bold;">';
                            _e('⚠️ La API respondió correctamente, pero no encontró servicios en la ubicación especificada.', 'ewo-location-services');
                            echo '</p>';
                            $recommendations[] = __('Prueba con diferentes coordenadas donde sepas que hay servicios disponibles.', 'ewo-location-services');
                            $recommendations[] = __('La API parece estar funcionando correctamente en términos de autenticación.', 'ewo-location-services');
                        } 
                        else {
                            echo '<p class="error-message" style="color: red; font-weight: bold;">';
                            _e('❌ Error 404: El endpoint podría no existir o estar incorrectamente configurado.', 'ewo-location-services');
                            echo '</p>';
                            $recommendations[] = __('Verifica que la URL del endpoint es correcta.', 'ewo-location-services');
                            $recommendations[] = __('Consulta con el proveedor de la API para confirmar la URL del servicio.', 'ewo-location-services');
                        }
                    }
                    else if ($status_code == 401 || $status_code == 403) {
                        echo '<p class="error-message" style="color: red; font-weight: bold;">';
                        _e('❌ Error de autenticación: La API rechazó la API key proporcionada.', 'ewo-location-services');
                        echo '</p>';
                        $recommendations[] = __('Verifica que la API key está correctamente configurada.', 'ewo-location-services');
                        $recommendations[] = __('Prueba con diferentes formatos de la API key (con guiones, sin guiones, mayúsculas).', 'ewo-location-services');
                        $recommendations[] = __('Prueba diferentes métodos de autenticación (encabezado vs. cuerpo).', 'ewo-location-services');
                    }
                    else {
                        echo '<p class="warning-message" style="color: orange; font-weight: bold;">';
                        _e('⚠️ Respuesta no estándar: La API respondió con un código no esperado.', 'ewo-location-services');
                        echo '</p>';
                        $recommendations[] = __('Examina los detalles de la respuesta para determinar el problema.', 'ewo-location-services');
                        $recommendations[] = __('Contacta al proveedor de la API para obtener ayuda con este código de respuesta.', 'ewo-location-services');
                    }
                    
                    // Mostrar recomendaciones específicas
                    if (!empty($recommendations)) {
                        echo '<h4>' . __('Recomendaciones específicas:', 'ewo-location-services') . '</h4>';
                        echo '<ul>';
                        foreach ($recommendations as $recommendation) {
                            echo '<li>' . esc_html($recommendation) . '</li>';
                        }
                        echo '</ul>';
                    }
                    
                    // Recomendaciones generales para solución de problemas
                    if ($status_code != 200) {
                        echo '<h4>' . __('Siguientes pasos recomendados:', 'ewo-location-services') . '</h4>';
                        echo '<ol>';
                        echo '<li>' . __('Prueba todos los métodos de autenticación disponibles en esta página.', 'ewo-location-services') . '</li>';
                        echo '<li>' . __('Si encuentras un método que funciona, utiliza ese código para actualizar el plugin.', 'ewo-location-services') . '</li>';
                        echo '<li>' . __('Si ningún método funciona, contacta al proveedor de la API para confirmar los detalles de autenticación.', 'ewo-location-services') . '</li>';
                        echo '</ol>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="notice notice-error">
            <p><?php _e('La API no está correctamente configurada. Por favor, configura la API key y la URL del endpoint primero.', 'ewo-location-services'); ?></p>
        </div>
        
        <p>
            <a href="?page=ewo-location-services" class="button button-primary">
                <?php _e('Ir a Configuración', 'ewo-location-services'); ?>
            </a>
        </p>
    <?php endif; ?>
</div>
