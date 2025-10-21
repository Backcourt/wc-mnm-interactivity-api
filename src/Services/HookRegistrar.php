<?php
/**
 * Plugin Hookable Interface
 *
 * @package Backcourt\MixAndMatch\iAPI\Services
 */

namespace Backcourt\MixAndMatch\iAPI\Services;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Auryn\Injector;

class HookRegistrar {

	/**
	 * Stores all registered WordPress actions.
	 *
	 * Each entry is an associative array with keys:
	 * - 'hook'          (string) Hook name.
	 * - 'class'         (string) Fully qualified class name.
	 * - 'method'        (string) Method name to call on the class.
	 * - 'priority'      (int) Hook priority, default 10.
	 * - 'accepted_args' (int) Number of accepted arguments, default 1.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	protected array $actions = array();

	/**
	 * Stores all registered WordPress filters.
	 *
	 * Each entry is an associative array with keys:
	 * - 'hook'          (string) Hook name.
	 * - 'class'         (string) Fully qualified class name.
	 * - 'method'        (string) Method name to call on the class.
	 * - 'priority'      (int) Hook priority, default 10.
	 * - 'accepted_args' (int) Number of accepted arguments, default 1.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	protected array $filters = array();

	/**
	 * Register an action hook callback.
	 *
	 * The callback will be executed lazily by Auryn Injector when the hook fires.
	 *
	 * @param string $hook          The WordPress action hook name.
	 * @param string $class         Fully qualified class name containing the method.
	 * @param string $method        Method name to be called on the class instance.
	 * @param int    $priority      Optional. Hook priority. Default 10.
	 * @param int    $accepted_args Optional. Number of accepted arguments. Default 1.
	 */
	public function add_action(
		string $hook,
		string $class,
		string $method,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = compact( 'hook', 'class', 'method', 'priority', 'accepted_args' );
	}

	/**
	 * Register a filter hook callback.
	 *
	 * The callback will be executed lazily by Auryn Injector when the hook fires.
	 *
	 * @param string $hook          The WordPress filter hook name.
	 * @param string $class         Fully qualified class name containing the method.
	 * @param string $method        Method name to be called on the class instance.
	 * @param int    $priority      Optional. Hook priority. Default 10.
	 * @param int    $accepted_args Optional. Number of accepted arguments. Default 1.
	 */
	public function add_filter(
		string $hook,
		string $class,
		string $method,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->filters[] = compact( 'hook', 'class', 'method', 'priority', 'accepted_args' );
	}

	/**
	 * Register all stored actions and filters with WordPress hooks.
	 *
	 * Uses Auryn Injector to lazily instantiate classes and execute methods when hooks fire.
	 *
	 * @param Injector $injector Auryn Injector instance for dependency injection.
	 */
	public function run( Injector $injector ): void {
		foreach ( $this->actions as $action ) {
			add_action(
				$action['hook'],
				function ( ...$args ) use ( $injector, $action ) {
					return $injector->execute( array( $action['class'], $action['method'] ), $args );
				},
				$action['priority'],
				$action['accepted_args']
			);
		}

		foreach ( $this->filters as $filter ) {
			add_filter(
				$filter['hook'],
				function ( ...$args ) use ( $injector, $filter ) {
					return $injector->execute( array( $filter['class'], $filter['method'] ), $args );
				},
				$filter['priority'],
				$filter['accepted_args']
			);
		}
	}
}
