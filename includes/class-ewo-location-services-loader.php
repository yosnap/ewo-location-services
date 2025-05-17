<?php
/**
 * Registra todos los hooks del plugin.
 *
 * Esta clase es responsable de mantener una lista de todos los hooks
 * (acciones y filtros) que son registrados por el plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

class Ewo_Location_Services_Loader {

    /**
     * El array de acciones registradas con WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    Las acciones registradas con WordPress para ejecutar cuando se carga el plugin.
     */
    protected $actions;

    /**
     * El array de filtros registrados con WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    Los filtros registrados con WordPress para ejecutar cuando se carga el plugin.
     */
    protected $filters;

    /**
     * Inicializa las colecciones utilizadas para mantener las acciones y filtros.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Añade una nueva acción al array $actions a registrar con WordPress.
     *
     * @since    1.0.0
     * @param    string     $hook          El nombre de la acción de WordPress que está siendo registrada.
     * @param    object     $component     Una referencia a la instancia del objeto en el que la acción es definida.
     * @param    string     $callback      El nombre de la función definida en el $component.
     * @param    int        $priority      Opcional. La prioridad en la que debe ser ejecutada la función callback. Por defecto es 10.
     * @param    int        $accepted_args Opcional. El número de argumentos que la función callback acepta. Por defecto es 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Añade un nuevo filtro al array $filters a registrar con WordPress.
     *
     * @since    1.0.0
     * @param    string     $hook          El nombre del filtro de WordPress que está siendo registrado.
     * @param    object     $component     Una referencia a la instancia del objeto en el que el filtro es definido.
     * @param    string     $callback      El nombre de la función definida en el $component.
     * @param    int        $priority      Opcional. La prioridad en la que debe ser ejecutada la función callback. Por defecto es 10.
     * @param    int        $accepted_args Opcional. El número de argumentos que la función callback acepta. Por defecto es 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Una utilidad para registrar los hooks en la colección apropiada.
     *
     * @since    1.0.0
     * @access   private
     * @param    array      $hooks         La colección de hooks que está siendo registrada (acciones o filtros).
     * @param    string     $hook          El nombre del hook de WordPress que está siendo registrado.
     * @param    object     $component     Una referencia a la instancia del objeto en el que la función es definida.
     * @param    string     $callback      El nombre de la función definida en el $component.
     * @param    int        $priority      La prioridad en la que debe ser ejecutada la función callback.
     * @param    int        $accepted_args El número de argumentos que la función callback acepta.
     * @return   array                     La colección de acciones y filtros registrados con WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Registra los filtros y acciones con WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}
