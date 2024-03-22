<?php

/**
 * Class UcLoader
 * Service class for load actions and filters.
 */
class UcLoader {
    /**
     * @var array
     */
    protected $actions;

    /**
     * @var array
     */
    protected $filters;

    public function __construct() {
        $this->actions = [];
        $this->filters = [];
    }

    /**
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int $priority
     * @param int $accepted_args
     *
     * @return void
     */
    public function add_action( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    public function add_filter( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    public function run(): void {
        foreach ( $this->filters as $hook ) {
            \add_filter( $hook['hook'], [
                $hook['component'],
                $hook['callback']
            ], $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            \add_action( $hook['hook'], [
                $hook['component'],
                $hook['callback']
            ], $hook['priority'], $hook['accepted_args'] );
        }
    }

    /**
     * @param array $hooks
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int $priority
     * @param int $accepted_args
     *
     * @return array
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ): array {
        $hooks[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];

        return $hooks;
    }
}
