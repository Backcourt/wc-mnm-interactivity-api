<?php
/**
 * Plugin Name: WC Mix and Match - Interactivity API
 * Plugin URI: https://github.com/backcourt/wc-mnm-interactivity-api
 * Description: Explore Interactivity API blocks for Mix and Match add to cart.
 * Version:           1.0.0-alpha.1
 * Author: Backcourt Development
 * Author URI: http://kathyisawesome.com
 * Requires PHP: 8.0
 * Requires at least: 6.7.0
 * Tested up to: 6.6.0
 * WC requires at least: 9.0.0
 * WC tested up to: 9.5.0
 *
 * GitHub Plugin URI: https://github.com/backcourt/wc-mnm-interactivity-api
 * Primary Branch: trunk
 * Release Asset: true
 *
 * Requires Plugins: woocommerce, woocommerce-mix-and-match-products
 *
 * Text Domain: wc-mnm-interactivity-api
 * Domain Path: /languages/
 *
 * @package Backcourt\MixAndMatch\iAPI
 *
 * Copyright: © 2025 Backcourt Development.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

use Backcourt\MixAndMatch\iAPI\Plugin;

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WC_MNM_INTERACTIVITY_API_FILE', __FILE__ );
define( 'WC_MNM_INTERACTIVITY_API_DIR', __DIR__ );
define( 'WC_MNM_INTERACTIVITY_API_VERSION', '1.0.0-alpha.2' );
define( 'WC_MNM_INTERACTIVITY_API_MANIFEST_FILE', __DIR__ . '/build/blocks-manifest.php' );

// If the GitHub repo was installed without running `composer install` to add the dependencies, the autoload will fail.
try {
	require_once __DIR__ . '/packages/autoload.php';
} catch ( Throwable $error ) {
	$display_error_notice = function () {
		echo '<div class="notice notice-error"><p><b>WC Mix and Match - Interactivity API error: Required dependencies are not installed.</b> Please run <code>composer install</code> in the plugin directory.</p></div>';
	};
	add_action( 'admin_notices', $display_error_notice );
	return;
}

// Load the template helper functions.
require_once __DIR__ . '/includes/template-functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
Plugin::get_instance()->run();
