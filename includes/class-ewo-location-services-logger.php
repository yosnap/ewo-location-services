<?php
/**
 * Clase que proporciona funcionalidad de registro (logging) para el plugin.
 *
 * Esta clase maneja todos los registros de eventos, errores y depuración para el plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

class Ewo_Location_Services_Logger {

    /**
     * Tipos de registro permitidos.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $log_types    Tipos de registro permitidos: INFO, WARNING, ERROR, DEBUG.
     */
    private $log_types = array('INFO', 'WARNING', 'ERROR', 'DEBUG');

    /**
     * Nombre del archivo de log.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $log_file    Ruta completa al archivo de log.
     */
    private $log_file;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Establecer el nombre del archivo de log con la fecha actual
        $this->log_file = EWO_LOCATION_SERVICES_LOGS_DIR . 'ewo-location-services-' . date('Y-m-d') . '.log';
        
        // Crear el directorio de logs si no existe
        if (!file_exists(EWO_LOCATION_SERVICES_LOGS_DIR)) {
            wp_mkdir_p(EWO_LOCATION_SERVICES_LOGS_DIR);
        }

        // Asegurarse de que el archivo de log existe
        if (!file_exists($this->log_file)) {
            $this->write_header();
        }
    }

    /**
     * Escribe una entrada de cabecera en el archivo de log.
     *
     * @since    1.0.0
     * @access   private
     */
    private function write_header() {
        $header = "==========================================================\n";
        $header .= "Ewo Location Services - Log File\n";
        $header .= "Date: " . date('Y-m-d') . "\n";
        $header .= "==========================================================\n\n";
        
        file_put_contents($this->log_file, $header, FILE_APPEND);
    }

    /**
     * Registra un mensaje en el archivo de log.
     *
     * @since    1.0.0
     * @param    string    $message    El mensaje a registrar.
     * @param    string    $type       El tipo de mensaje (INFO, WARNING, ERROR, DEBUG).
     */
    public function log($message, $type = 'INFO') {
        // Asegurarse de que el tipo de log es válido
        $type = in_array(strtoupper($type), $this->log_types) ? strtoupper($type) : 'INFO';
        
        // Formatear el mensaje con timestamp y tipo
        $log_entry = sprintf("[%s] [%s]: %s\n", date('Y-m-d H:i:s'), $type, $message);
        
        // Escribir en el archivo
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }

    /**
     * Registra un mensaje de tipo INFO.
     *
     * @since    1.0.0
     * @param    string    $message    El mensaje a registrar.
     */
    public function info($message) {
        $this->log($message, 'INFO');
    }

    /**
     * Registra un mensaje de tipo WARNING.
     *
     * @since    1.0.0
     * @param    string    $message    El mensaje a registrar.
     */
    public function warning($message) {
        $this->log($message, 'WARNING');
    }

    /**
     * Registra un mensaje de tipo ERROR.
     *
     * @since    1.0.0
     * @param    string    $message    El mensaje a registrar.
     */
    public function error($message) {
        $this->log($message, 'ERROR');
    }

    /**
     * Registra un mensaje de tipo DEBUG.
     *
     * @since    1.0.0
     * @param    string    $message    El mensaje a registrar.
     */
    public function debug($message) {
        $this->log($message, 'DEBUG');
    }

    /**
     * Obtiene el contenido del archivo de log actual.
     *
     * @since    1.0.0
     * @return   string    El contenido del archivo de log.
     */
    public function get_log_content() {
        if (file_exists($this->log_file)) {
            return file_get_contents($this->log_file);
        }
        return 'No hay registros disponibles.';
    }

    /**
     * Obtiene la ruta al archivo de log actual.
     *
     * @since    1.0.0
     * @return   string    La ruta completa al archivo de log.
     */
    public function get_log_file_path() {
        return $this->log_file;
    }

    /**
     * Limpia (elimina) el archivo de log actual.
     *
     * @since    1.0.0
     * @return   boolean   Verdadero si se limpió correctamente, falso en caso contrario.
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            // Reemplazar el contenido con solo el encabezado
            $this->write_header();
            return true;
        }
        return false;
    }

    /**
     * Obtiene una lista de todos los archivos de log disponibles.
     *
     * @since    1.0.0
     * @return   array    Lista de archivos de log disponibles.
     */
    public function get_log_files() {
        $log_files = array();
        
        if (is_dir(EWO_LOCATION_SERVICES_LOGS_DIR)) {
            $files = scandir(EWO_LOCATION_SERVICES_LOGS_DIR);
            
            foreach ($files as $file) {
                if (preg_match('/^ewo-location-services-\d{4}-\d{2}-\d{2}\.log$/', $file)) {
                    $log_files[] = $file;
                }
            }
        }
        
        // Ordenar por fecha más reciente primero
        rsort($log_files);
        
        return $log_files;
    }
}
