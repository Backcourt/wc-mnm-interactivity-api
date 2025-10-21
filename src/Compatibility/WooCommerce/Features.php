<?php
/**
 * WooCommerce Features Compatibility.
 *
 * @package Backcourt\MixAndMatch\iAPI\Compatibility\WooCommerce
 */
namespace Backcourt\MixAndMatch\iAPI\Compatibility\WooCommerce;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\Hookable;
use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;

/**
 * Features init.
 */
class Features implements Hookable {

	/**
	 * Init hooks
	 *
	 * @param HookRegistrar $registrar The central hook registration object.
	 */
	public static function register_hooks( HookRegistrar $registrar ): void {
		$registrar->add_action( 'before_woocommerce_init', self::class, 'declare_compat', 10, 2 );
	}

	/**
	 * Declare compatibility with WooCommerce features.
	 */
	public function declare_compat(): void {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		// HPOS (Custom Order tables) compatibility.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( WC_MNM_INTERACTIVITY_API_FILE ), true );

		// Cart and Checkout Blocks.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', plugin_basename( WC_MNM_INTERACTIVITY_API_FILE ), true );
	}
}
