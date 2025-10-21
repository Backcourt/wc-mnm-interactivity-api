<?php
/**
 * WooCommerce Add to Cart With Options Block Modifications.
 *
 * @package Backcourt\MixAndMatch\iAPI\Features
 */

namespace Backcourt\MixAndMatch\iAPI\Features;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\Hookable;
use Backcourt\MixAndMatch\iAPI\Services\HookRegistrar;

/**
 * Modify the Add to Cart With Options Block.
 */
class AddToCartWithOptions implements Hookable {

	/**
	 * Init hooks
	 *
	 * @param HookRegistrar $registrar The central hook registration object.
	 */
	public static function register_hooks( HookRegistrar $registrar ): void {
		$registrar->add_filter( '__experimental_woocommerce_mix-and-match_add_to_cart_with_options_block_template_part', self::class, 'get_template_part_path', 10, 2 );
		$registrar->add_filter( '__experimental_woocommerce_variable-mix-and-match__add_to_cart_with_options_block_template_part', self::class, 'get_variable_template_part_path', 10, 2 );
		$registrar->add_filter( 'block_type_metadata', self::class, 'modify_metadata' );
		$registrar->add_filter( 'render_block_context', self::class, 'provide_context', 1, 2 );
	}

	/**
	 * Register Mix and Match template part path
	 *
	 * @param string $template_part_path
	 * @param string $product_type
	 * @return string
	 */
	public function get_template_part_path( string $template_part_path, string $product_type ): string {
		return WC_MNM_INTERACTIVITY_API_DIR . '/templates/' . $product_type . '-product-add-to-cart-with-options.html';
	}

	/**
	 * Register Variable Mix and Match template part path
	 *
	 * @param string $template_part_path
	 * @param string $product_type
	 * @return string
	 */
	public function get_variable_template_part_path( string $template_part_path, string $product_type ): string {
		return BlockTemplateUtils::DIRECTORY_NAMES['TEMPLATE_PARTS'] . '/variable-product-add-to-cart-with-options.html';
	}

	/**
	 * Modify the block metadata to include interactivity support.
	 *
	 * @param array $metadata Metadata for registering a block type.
	 * @return array
	 */
	public function modify_metadata( $metadata ) {
		if ( 'woocommerce/add-to-cart-with-options' === $metadata['name'] ) {
			$metadata['providesContext']                               ??= array();
			$metadata['providesContext']['wc-mix-and-match/containerId'] = 'containerId';
			$metadata['supports']                                      ??= array();
			$metadata['supports']['interactivity']                       = true;
		}
		return $metadata;
	}

	/**
	 * Add a custom context to the rendered block.
	 *
	 * NB: We need this because do_blocks() cannot inherit any block context from the parent block.
	 *
	 * @param array $context      Default context.
	 * @param array $parsed_block {
	 *     An associative array of the block being rendered. See WP_Block_Parser_Block.
	 *
	 *     @type string   $blockName    Name of block.
	 *     @type array    $attrs        Attributes from block comment delimiters.
	 *     @type array[]  $innerBlocks  List of inner blocks. An array of arrays that
	 *                                  have the same structure as this one.
	 *     @type string   $innerHTML    HTML from inside block comment delimiters.
	 *     @type array    $innerContent List of string fragments and null markers where
	 *                                  inner blocks were found.
	 * }
	 *
	 * return array - The modified context.
	 */
	public function provide_context( $context, $parsed_block ) {

		if ( 'woocommerce/add-to-cart-with-options' === $parsed_block['blockName'] ) {

			$product = wc_get_product( $context['postId'] ?? 0 );

			if ( ! $product ) {
				return $context;
			}

			$container_id = 0;

			if ( $product->is_type( 'mix-and-match' ) ) {
				$container_id = intval( $context['postId'] );
			} elseif ( $product->is_type( 'variable-mix-and-match' ) ) {

				if ( isset( $_GET['mnm_variation_id'] ) ) {
					$container_id = intval( $_GET['mnm_variation_id'] );
				} else {
					$attributes = $product->get_default_attributes();

					foreach ( $attributes as $key => $value ) {
						$attributes[ 'attribute_' . $key ] = $value;
						unset( $attributes[ $key ] );
					}

					$data_store        = \WC_Data_Store::load( 'product' );
					$default_variation = $data_store->find_matching_product_variation( $product, $attributes ); // @todo - can this be handled on SAVE?

					if ( $default_variation ) {
						$container_id = $default_variation;
					}
				}
			}

			$context['wc-mix-and-match/containerId'] = $container_id;

			/**
			 * Because we are printing the template part using do_blocks, context from the outside is lost.
			 * The following is a workaround to persist the context for any child block.
			 */
			$filter_block_context = static function ( $context ) use ( $container_id ) {
				$context['wc-mix-and-match/containerId'] = $container_id;
				return $context;
			};

			// Use an early priority to so that other 'render_block_context' filters have access to the values.
			add_filter( 'render_block_context', $filter_block_context );

			// Unhook the context filter above, after rendering this `core/post-template` block.
			$unhook = function ( $content ) use ( &$unhook, $filter_block_context ) {
				// Unhook.
				remove_filter( 'render_block_context', $filter_block_context );
				return $content;
			};

			remove_filter( 'render_block_context', $unhook );

			// Set interactivity state before render.
			wp_interactivity_state(
				'wc-mix-and-match/add-to-cart',
				array(
					'minContainerSize' => 10,
				)
			);

		}

		return $context;
	}
}
