<?php
/**
 * Register blocks and patterns.
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\Hookable;
use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;
use Backcourt\MixAndMatch\iAPI\Plugin;

/**
 * Blocks init.
 */
class Controller implements Hookable {

	/**
	 * Init hooks
	 * 
	 * @since 1.0.0
	 *
	 * @param HookRegistrar $registrar The central hook registration object.
	 */
	public static function register_hooks( HookRegistrar $registrar ): void {
		$registrar->add_action( 'init', self::class, 'register_blocks' );
		$registrar->add_action( 'init', self::class, 'register_patterns' );
	}

	/**
	 * Universal render block callback
	 * 
	 * @since 1.0.0
	 *
	 * @param string $className Fully qualified class name of the block to render.
	 * @return string
	 */
	public static function render_block( string $className, $attributes, $content, $block ): void {
		Plugin::make( $className )->render_block( $attributes, $content, $block );
	}

	/**
	 * Register all blocks
	 * 
	 * @since 1.0.0
	 *
	 * @throws Exception Object doesn't exist.
	 */
	public function register_blocks(): void {

		// If the GitHub repo was installed without running `npm run build` to generate the blocks the block registration will fail.
		if ( ! file_exists( WC_MNM_INTERACTIVITY_API_MANIFEST_FILE ) ) {
			$display_error_notice = function () {
				echo '<div class="notice notice-error"><p><b>WC Mix and Match - Interactivity API error: Blocks are not built.</b> Please run <code>npm install && npm run build</code> in the plugin directory.</p></div>';
			};
			add_action( 'admin_notices', $display_error_notice );
			return;
		}

		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
		 * based on the registered block metadata.
		 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
		 *
		 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
		 */
		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
			wp_register_block_types_from_metadata_collection( WC_MNM_INTERACTIVITY_API_DIR . '/build/Blocks', WC_MNM_INTERACTIVITY_API_MANIFEST_FILE );
			return;
		}

		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` file.
		 * Added to WordPress 6.7 to improve the performance of block type registration.
		 *
		 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
		 */
		if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( WC_MNM_INTERACTIVITY_API_DIR . '/build/Blocks', WC_MNM_INTERACTIVITY_API_MANIFEST_FILE );
		}
		/**
		 * Registers the block type(s) in the `blocks-manifest.php` file.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_block_type/
		 */
		$manifest_data = require WC_MNM_INTERACTIVITY_API_MANIFEST_FILE;
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			register_block_type( WC_MNM_INTERACTIVITY_API_DIR . "/build/Blocks/{$block_type}" );
		}
	}


	/**
	 * Initialize patterns
	 * 
	 * @since 1.0.0
	 *
	 * @throws Exception Object doesn't exist.
	 */
	public function register_patterns(): void {

		ob_start();
		include WC_MNM_INTERACTIVITY_API_DIR . '/templates/mnm.php';
		$pattern = ob_get_clean();

		register_block_pattern(
			'wc-mix-and-match/add-to-cart',
			array(
				'title'    => esc_html__( 'WooCommerce Mix and Match', 'wc-mnm-interactivity-api' ),
				'inserter' => false,
				'content'  => $pattern,
				'category' => 'woocommerce',
			)
		);
	}
}
