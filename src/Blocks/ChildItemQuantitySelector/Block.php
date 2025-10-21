<?php
/**
 * Mix and Match Child Item Quantity Selector Block
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks\ChildItemQuantitySelector;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\BlockTypes\AddToCartWithOptions\Utils as AddToCartWithOptionsUtils;
use Automattic\WooCommerce\Blocks\Utils\StyleAttributesUtils;
use Backcourt\MixAndMatch\iAPI\Interfaces\RenderBlock;

/**
 * Block Name: Mix and Match Child Items
 */
class Block implements RenderBlock {

	/**
	 * Server rendering for this block
	 * 
	 * @since 1.0.0
	 *
	 * @param array    $attributes - The block attributes.
	 * @param string   $content - The block default content.
	 * @param WP_Block $block - The block instance.
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): void {

		$product = AddToCartWithOptionsUtils::get_product_from_context( $block, $GLOBALS['product'] );

		// If out of stock or not purchasable, return early.
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			// return wp_kses_post( $child_item->get_availability_html() ); // @todo - I think we might need this for tabular layout?
			return;
		}

		$container = wc_get_product( $block->context['wc-mix-and-match/containerId'] ?? 0 );

		if ( ! $container instanceof \WC_Product || ! wc_mnm_is_product_container_type( $container ) ) {
			return;
		}

		// Get the child item by product ID.
		$child_item = $container->get_child_item_by_product_id( $product->get_id() );

		if ( ! $child_item ) {
			return;
		}

		$classes_and_styles = StyleAttributesUtils::get_classes_and_styles_by_attributes( $attributes, array(), array( 'extra_classes' ) );

		$classes = implode(
			' ',
			array_filter(
				array(
					'wp-block-wc-mix-and-match-add-to-cart-child-item__quantity-selector',
					'wp-block-add-to-cart-with-options-quantity-selector wc-block-add-to-cart-with-options__quantity-selector',
					esc_attr( $classes_and_styles['classes'] ),
				)
			)
		);

		$wrapper_attributes = array(
			'class' => $classes,
			'style' => esc_attr( $classes_and_styles['styles'] ),
		);


		ob_start();
		\wc_mnm_template_child_item_quantity( $child_item, $container );

		$block_content = ob_get_clean();

		$is_stepper_style = 'stepper' === ( $attributes['quantitySelectorStyle'] ?? 'input' );

		if ( $is_stepper_style ) {
			$product      = $child_item->get_product();
			$product_name = $product->get_name();
			$block_content = AddToCartWithOptionsUtils::add_quantity_steppers( $block_content, $product_name );
			$block_content = AddToCartWithOptionsUtils::add_quantity_stepper_classes( $block_content );
		}

		// Remove the label because we are rendering one as a separate block.
		// @todo - Add a label to the title block instead?
		$block_content = $this->remove_quantity_label( $block_content );
		$block_content = $this->remove_buttons_added_class( $block_content );

		$input_attributes = array(); // what do we need here? maybe bind min/max/step to variable mix and match variation?

		$block_content = AddToCartWithOptionsUtils::make_quantity_input_interactive( $block_content, $wrapper_attributes, $input_attributes, $child_item->get_the_id() );

		echo $block_content;
	}

	/**
	 * Removes a legacy buttons_added class from the quantity input HTML
	 * 
	 * @since 1.0.0
	 *
	 * @param string $quantity_html The quantity input HTML.
	 * @return string The quantity input HTML without the label.
	 */
	private function remove_buttons_added_class( $quantity_html ) {
		$p = new \WP_HTML_Tag_Processor( $quantity_html );

		// Remove the buttons_added class from the quantity input wrapper.
		if ( $p->next_tag( array( 'class_name' => 'quantity' ) ) ) {
			$p->remove_class( 'buttons_added' );
		}

		return $p->get_updated_html();
	}

	/**
	 * Removes the label from quantity input HTML
	 * 
	 * @since 1.0.0
	 *
	 * @param string $quantity_html The quantity input HTML.
	 * @return string The quantity input HTML without the label.
	 */
	private function remove_quantity_label( $quantity_html ) {
		// Remove the label and aria-label from the quantity input.
		$quantity_html = preg_replace( '/<label[^>]*>.*?<\/label>/s', '', $quantity_html );
		return preg_replace( '/\s*aria-label="[^"]*"/', '', $quantity_html );
	}
}
