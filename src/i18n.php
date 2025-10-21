<?php
/**
 * Load text domain.
 *
 * @package Backcourt\MixAndMatch\iAPI
 */
namespace Backcourt\MixAndMatch\iAPI;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\Hookable;
use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;

/**
 * Translation init.
 */
class i18n implements Hookable {

	/**
	 * Init hooks
	 *
	 * @param HookRegistrar $registrar The central hook registration object.
	 */
	public static function register_hooks( HookRegistrar $registrar ): void {
		$registrar->add_action( 'init', self::class, 'load_plugin_textdomain' );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain(): void {

		load_plugin_textdomain(
			'wc-mix-and-match-interactivity-api',
			false,
			dirname( dirname( plugin_basename( WC_MNM_INTERACTIVITY_API_DIR ) ) ) . '/languages/'
		);
	}
}
