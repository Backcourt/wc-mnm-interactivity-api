<?php
/**
 * Mix and Match Child Item Temnplate Block
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks\ProductPrice;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\Hookable;
use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;
/**
 * Block Name: Mix and Match Child Item Template
 */
class Block implements Hookable {

	/**
	 * Init hooks
	 *
	 * @param HookRegistrar $registrar The central hook registration object.
	 */
	public static function register_hooks( HookRegistrar $registrar ): void {
		$registrar->add_action( 'block_type_metadata', self::class, 'modify_metadata' );
		$registrar->add_action( 'render_block_woocommerce/product-price', self::class, 'modify_block', 10, 3 );
	}


	/**
	 * Modify the block metadata to include interactivity support.
	 *
	 * @param array $metadata Metadata for registering a block type.
	 * @return array
	 */
	function modify_metadata( $metadata ) {
		if ( 'woocommerce/product-price' === $metadata['name'] ) {
			$metadata['usesContext'] = $metadata['usesContext'] ?? [];
			$metadata['usesContext'][] = 'wc-mix-and-match/containerId';	
			$metadata['supports'] = $metadata['supports'] ?? array();
			$metadata['supports']['interactivity'] = true;
		}
		return $metadata;
	}

	/**
	 * Add interactivity to block.
	 *
	 * @param string    $block_content The block content about to be rendered.
	 * @param array     $parsed_block The block being rendered.
	 * @param \WP_Block $instance      The block instance.
	 *
	 * @return string
	 */
	function modify_block( $block_content, $parsed_block, $instance ) {

		// Main product page's ID.
		$product_id = $instance->context['postId'] ?? 0;

		if ( is_admin() || ! $product_id ) {
			return $block_content;
		}

		$product = \wc_get_product( $product_id );

		if ( ! $product || ! wc_mnm_is_product_type( $product ) ) {
			return $block_content;
		}

		$p = new \WP_HTML_Tag_Processor( $block_content );

		if ( $p->next_tag( array( 'class_name' => 'wp-block-woocommerce-product-price' ) ) ) {

			wp_interactivity_state( 'woocommerce/add-to-cart-with-option', [
				'displayStatus' => 'none',
			] );

		//	$p->set_attribute( 'data-wp-interactive', 'wc-mix-and-match/add-to-cart/status' );
			$p->set_attribute( 'data-wp-watch', 'status::callbacks.updatePrice' ); // @todo because this block is outside the add to cart with options block, it doesn't have the container ID context
		}

		return $p->get_updated_html();
	}

}
