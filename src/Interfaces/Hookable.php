<?php
/**
 * Plugin Hookable Interface
 *
 * @package Backcourt\MixAndMatch\iAPI\Services
 */

namespace Backcourt\MixAndMatch\iAPI\Interfaces;

use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;

defined( 'ABSPATH' ) || exit;

interface Hookable {
	/**
	 * Called during plugin bootstrap to allow the class to declare hooks
	 *
	 * @param HookRegistrar $registrar The central hook registration object.
	 */
	public static function register_hooks( HookRegistrar $registrar ): void;
}
