<?php
/**
 * Mix and Match Child Item Temnplate Block
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks\StatusUI;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\RenderBlock;

/**
 * Block Name: Mix and Match Child Item Template
 */
class Block implements RenderBlock {

	/**
	 * Server rendering for this block
	 * 
	 * @since 1.0.0
	 *
	 * @param array    $attributes The block attributes.
	 * @param string   $content The block content.
	 * @param WP_Block $block The block instance.
	 * @return string The block content.
	 */
	public function render_block( $attributes, $content, $block ): void {

		$container_id = $block->context['wc-mix-and-match/containerId'] ?? 0;

		if ( ! isset( $container_id ) ) {
			return;
		}

		$product = \wc_get_product( $container_id );

		if ( ! \wc_mnm_is_product_container_type( $product ) ) {
			return;
		}

		if ( ! $product->has_child_items() ) {
			return;
		}

		wp_interactivity_state( 'woocommerce/add-to-cart-with-options', [
			'displayStatus' => 'none',
		] );

		ob_start();

		// @todo - build out status block to look like the mobile style footer as well. Hide/show the revelant parts based on the attributes.
		?>

		<div
			class="wc-mix-and-match__status wc-block-components-notices alignwide"
			data-wp-style--display="state.displayStatus"
		>

			<span class="wc-mix-and-match__total"><?php esc_html_e( 'Total:', 'wc-mix-and-match-iapi' ); ?></span>
			<span class="wc-mix-and-match__price" data-wp-watch="callbacks.updatePrice"></span>
			<span class="wc-mix-and-match__counter" data-wp-text="state.counterText"></span>

			<template data-wp-each--notice="state.validationNotices" data-wp-each-key="context.notice.id">
				<div
					class="wc-block-components-notice-banner"
					data-wp-class--is-error="state.isError"
					data-wp-class--is-success ="state.isSuccess"
					data-wp-class--is-info="state.isInfo"
					data-wp-bind--role="state.role"
				>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
						<path data-wp-bind--d="state.iconPath"></path>
					</svg>
					<div class="wc-block-components-notice-banner__content">
						<span data-wp-text="state.noticeContent"></span>
					</div>
					
				</div>
			</template>

		</div>

		<?php

		echo ob_get_clean();
	}
}
