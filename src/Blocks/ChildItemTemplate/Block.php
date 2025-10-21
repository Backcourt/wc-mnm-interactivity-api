<?php
/**
 * Mix and Match Child Item Temnplate Block
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks\ChildItemTemplate;

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
	 * @param array    $attributes - The block attributes.
	 * @param string   $content - The block default content.
	 * @param WP_Block $block - The block instance.
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): void {
		$container_id = $block->context['wc-mix-and-match/containerId'] ?? 0;

		if ( ! wc_mnm_container_has_child_items( $container_id ) ) {
			return;
		}

		// Enqueue WooCommerce's Product Template block style.
		if ( function_exists( 'wp_enqueue_block_style' ) ) {
			wp_enqueue_block_style( 'woocommerce/product-template', array() );
		}

		// @todo - maybe update thumbnail cache here?
		// @see: https://github.com/woocommerce/woocommerce/blob/6c1e6ce5f3a07b190ad648468bc14053eec51a69/plugins/woocommerce/src/Blocks/BlockTypes/ProductTemplate.php#L58
		global $post, $product;

		$classnames = array( 'wc-mix-and-match__child-items', 'wc-block-product-template' );

		if ( isset( $block->context['displayLayout'] ) ) {
			$classnames[] = 'is-product-collection-layout-' . $block->context['displayLayout']['type'] . ' ';

			if ( isset( $block->context['displayLayout']['type'] ) && 'flex' === $block->context['displayLayout']['type'] ) {
				if ( isset( $block->context['displayLayout']['shrinkColumns'] ) && $block->context['displayLayout']['shrinkColumns'] ) {
					$classnames[] = "wc-block-product-template__responsive columns-{$block->context['displayLayout']['columns']}";
				} else {
					$classnames[] = "is-flex-container columns-{$block->context['displayLayout']['columns']}";
				}
			}
		}

		if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
			$classnames[] = 'has-link-color';
		}

		// @todo - how can we prime this cache without needing to run new WP_Query again?
		// if ( $this->block_core_post_template_uses_featured_image( $block->inner_blocks ) ) {
			// update_post_thumbnail_cache( $query );
		// }

		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'role'  => 'list',
				'class' => implode( ' ', $classnames ),
			)
		);

		$content = '';

		foreach ( $product->get_child_items() as $child_item ) {

			$product    = $child_item->get_product();
			$product_id = $product->get_id();

			// Since this template uses the core/post-title block to show the product name
			// a temporary replacement of the global post is needed. This is reverted back
			// to its initial post value that is stored in the $previous_post variable.
			$post = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );

			// Get an instance of the current child item template block.
			$block_instance = $block->parsed_block;

			// Set the block name to one that does not correspond to an existing registered block.
			// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
			$block_instance['blockName'] = 'core/null';

			// Relay the block context to the inner blocks.
			$available_context = array_merge(
				(array) $block->context,
				array(
					'postType'                     => get_post_type(),
					'postId'                       => $product->get_id(),
					'wc-mix-and-match/containerId' => $container_id,
				)
			);

			// Render the inner blocks of the Post Template block with `dynamic` set to `false` to prevent calling
			// `render_callback` and ensure that no wrapper markup is included.
			$block_content = (
				new \WP_Block(
					$block_instance,
					$available_context
				)
			)->render( array( 'dynamic' => false ) );

			$context = array(
				'productId'   => $product_id,
				'containerId' => $container_id,
			);

			$li_directives = '
				data-wp-interactive="wc-mix-and-match/add-to-cart-child-item"
				data-wp-key="product-item-' . $product_id . '"
			';

			// Wrap the render inner blocks in a `li` element with the appropriate post classes.
			$post_classes = implode( ' ', get_post_class( 'wc-mix-and-match__child-item wc-block-product' ) );
			$content     .= strtr(
				'<li 
					role="listitem"
					class="{classes}"
					{li_directives}
					{li_context}
				>
					{content}
				</li>',
				array(
					'{classes}'       => esc_attr( $post_classes ),
					'{li_directives}' => $li_directives,
					'{li_context}'    => wp_interactivity_data_wp_context( $context ),
					'{content}'       => $block_content,
				)
			);

		}

		/*
		* Use this function to restore the context of the template tags
		* from a secondary query loop back to the main query loop.
		* Since we use two custom loops, it's safest to always restore.
		*/
		wp_reset_postdata();

		printf(
			'<ul %1$s>%2$s</ul>',
			$wrapper_attributes,
			$content
		);
	}


	/**
	 * Determines whether a block list contains a block that uses the featured image.
	 *
	 * @param WP_Block_List $inner_blocks Inner block instance.
	 *
	 * @return bool Whether the block list contains a block that uses the featured image.
	 */
	protected function block_core_post_template_uses_featured_image( $inner_blocks ) {
		foreach ( $inner_blocks as $block ) {
			if ( 'core/post-featured-image' === $block->name ) {
				return true;
			}
			if (
				'core/cover' === $block->name &&
				! empty( $block->attributes['useFeaturedImage'] )
			) {
				return true;
			}
			if ( $block->inner_blocks && block_core_post_template_uses_featured_image( $block->inner_blocks ) ) {
				return true;
			}
		}

		return false;
	}
}
