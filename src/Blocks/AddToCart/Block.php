<?php
/**
 * Mix and Match Add To Cart Wrapper Block
 *
 * @package Backcourt\MixAndMatch\iAPI\Blocks
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Backcourt\MixAndMatch\iAPI\Blocks\AddToCart;

defined( 'ABSPATH' ) || exit;

use Backcourt\MixAndMatch\iAPI\Interfaces\RenderBlock;
use Automattic\WooCommerce\Blocks\BlockTypes\AddToCartWithOptions\Utils as AddToCartWithOptionsUtils;
use Automattic\WooCommerce\Blocks\Utils\StyleAttributesUtils;

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

		// Container ID and Product Id can differ when the container is a product variation.
		$product_id   = $instance->context['postId'] ?? 0;
		$container_id   = $instance->context['wc-mix-and-match/containerId'] ?? 0;

		$product = AddToCartWithOptionsUtils::get_product_from_context( $block, $GLOBALS['product'] );

		if ( ! $product || ! $product->is_type( [ 'mix-and-match', 'variable-mix-and-match' ] ) ) {
			return;
		}

		$container     = $container_id === $product_id ? $product : wc_get_product( $container_id );

		if ( ! $container instanceof \WC_Product || ! wc_mnm_is_product_container_type( $container ) ) {
			return;
		}

		if ( ! $container->has_child_items() ) {
			return;
		}

		// Add context for purchasable child products.
		$children_product_data = array();
		foreach ( $product->get_child_items() as $child_item ) {
			$child_product = $child_item->get_product();
			if ( $child_product && $child_product->is_purchasable() && $child_product->is_in_stock() ) {

				$children_product_data[ $child_product->get_id() ] = array(
					'min'  => $child_item->get_quantity( 'min' ) ?? 0,
					'max'  => $child_item->get_quantity( 'max' ) ?? null,
					'step' => $child_item->get_quantity( 'step' ) ?? 1,
					'type' => $child_product->get_type(),
				);
			}
		}

		$context['groupedProductIds'] = array_keys( $children_product_data ); // @todo - replace groupedProductIds with childProductIds? do we need this at all?
		
		\wp_interactivity_config(
			'woocommerce',
			array(
				'products' => $children_product_data,
			)
		);

		// Add quantity context for purchasable child products.
		$context['quantity'] = array_fill_keys(
			$context['groupedProductIds'],
			0
		);

		// Set default quantity for each child product.
		foreach ( $context['groupedProductIds'] as $child_product_id ) {
			$child_product = wc_get_product( $child_product_id );
			if ( $child_product ) {

				$default_child_quantity = isset( $_POST['quantity'][ $child_product->get_id() ] ) ? wc_stock_amount( wc_clean( wp_unslash( $_POST['quantity'][ $child_product->get_id() ] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$context['quantity'][ $child_product_id ] = $default_child_quantity;

				// Check for any "sold individually" products and set their default quantity to 0.
				if ( $child_product->is_sold_individually() ) {
					$context['quantity'][ $child_product_id ] = 0;
				}
			}
		}

		// Set interactivity configuration for the block.
		\wp_interactivity_config(
			'woocommerce/add-to-cart-with-options',
			\WC_Mix_and_Match()->display->get_add_to_cart_parameters(),
		);

		$classes_and_styles = StyleAttributesUtils::get_classes_and_styles_by_attributes( $attributes, array(), array( 'extra_classes' ) );

		$classes = implode(
			' ',
			array_filter(
				array(
					'wc-mix-and-match__container',
					esc_attr( $classes_and_styles['classes'] ),
				)
			)
		);

		// Set initial state for the block.
		\wp_interactivity_state(
			'woocommerce/add-to-cart-with-options',
			array(
				'maxContainerSize' => $container->get_min_container_size(),
				'minContainerSize' => $container->get_max_container_size(),
				'isLoading' => true,
			)
		);

		ob_start();
		?>
		<div
			<?php
				echo \get_block_wrapper_attributes(
					array(
						'class' => $classes,
						'style' => esc_attr( $classes_and_styles['styles'] ),
					)
				);
				?>
			data-wp-interactive="woocommerce/add-to-cart-with-options"
			<?php echo \wp_interactivity_data_wp_context( $this->get_block_context( $container, $product ) ); ?>
			data-wp-init="callbacks.init"
			data-wp-watch="callbacks.watch"
		>
			<?php echo $content; ?>
		</div>
		<?php
		$add_to_cart = ob_get_clean();

		$add_to_cart = $this->wrap_status_ui_store_region( $add_to_cart );

		echo $add_to_cart;
	}

	/**
	 * Get the data attributes without MNM's data- prefix
	 * 
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return array
	 */
	private function get_block_data_attributes( $product, $args = array() ) {
		$attributes = wp_parse_args(
			$args,
			array(
				'per_product_pricing' => $product->is_priced_per_product(),
				'product_id'          => $product->get_id(),
				'container_id'        => $product->get_id(),
				'min_container_size'  => $product->get_min_container_size(),
				'max_container_size'  => $product->get_max_container_size(),
				'base_price'          => \wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ),
				'base_regular_price'  => \wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ),
				'price_data'          => $this->get_container_price_data( $product ),
				'input_name'          => \wc_mnm_get_child_input_name( $product->get_id() ),
				'validation_context'  => isset( $_GET['update-container'] ) ? 'cart' : 'add-to-cart',
			)
		);

		/**
		 * `wc_mnm_container_data_attributes` Data attribues filter.
		 *
		 * @param  array $attributes
		 * @param  obj WC_Product_Mix_and_Match $product
		 */
		return (array) apply_filters( 'wc_mnm_container_data_attributes', wp_parse_args( $args, $attributes ), $product );
	}

	/**
	 * Get the relevant block context for the Add to Cart block
	 * 
	 * @since 1.0.0
	 *
	 * @param WC_Product $product The container product object.
	 * @param WC_Product $parent_product The parent product object.
	 * @return array
	 */
	private function get_block_context( $container, $parent_product ) : array {
		$configuration       = $this->get_container_configuration( $container );
		$container_data      = $this->get_block_data_attributes( $container );
		
		$prefetch_variations = count( $parent_product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $parent_product );

		return array(
			'containerId'       => $container->get_id(),
			'container'         => $container_data,
			'containers'        => [ $container->get_id() => $container_data ],
			'config'            => $configuration,
			'getContainerSize'  => array_sum( $configuration ),
			'hasSelections'     => array_sum( $configuration ) > 0,
			'isLoading'	        => true,
			'productId'         => $parent_product->get_id(),
			'subTotals'         => [],
			'validationNotices' => [],
			'variationIds'      => $prefetch_variations ? array_map( 'intval', $parent_product->get_children() ) : [], // @todo - limit to available children.
		);
	}

	/**
	 * Get server side configuration for the container
	 * 
	 * @since 1.0.0
	 *
	 * @param WC_Product $product The container product object.
	 * @param WC_Product $parent_product The parent product object.
	 * @return array
	 */
	private function get_container_configuration( $container ) : array {
		$configuration       = \WC_Mix_and_Match()->cart->get_posted_container_configuration( $container->get_id() );
		return wp_list_pluck( $configuration, 'quantity' );
	}

	/**
	 * Get price configuration for the container
	 * 
	 * @since 1.0.0
	 * 
	 * @todo format these prices with prepare_money_response, incl:excl tax, etc
	 *
	 * @param WC_Product $product The container product object.
	 * @return array
	 */
	private function get_container_price_data( $container ) : array {
		$container_price_data = array();

		$num_decimals = \wc_get_price_decimals();

		$raw_container_price_min = $container->get_container_price( 'min', true );
		$raw_container_price_max = $container->get_container_price( 'max', true );

		$container_price_data['per_product_pricing'] = $container->is_priced_per_product();

		$container_price_data['raw_container_price_min'] = $this->prepare_money_response( $raw_container_price_min, $num_decimals );
		$container_price_data['raw_container_price_max'] = '' === $raw_container_price_max ? '' : $this->prepare_money_response( $raw_container_price_max, $num_decimals );

		$container_price_data['price_string']   = '%s';
		$container_price_data['is_purchasable'] = $container->is_purchasable();
		$container_price_data['is_in_stock']    = $container->is_in_stock();

		$container_price_data['show_free_string'] = ( $container->is_priced_per_product() ? apply_filters( 'wc_mnm_show_free_string', false, $container ) : true );

		$container_price_data['prices']         = array();
		$container_price_data['regular_prices'] = array();

		$container_price_data['prices_tax'] = array();

		$container_price_data['quantities'] = array();

		$container_price_data['product_ids'] = array();

		$container_price_data['is_sold_individually'] = array();

		$container_price_data['base_price']         = $this->prepare_money_response( $container->get_price(), $num_decimals );
		$container_price_data['base_regular_price'] = $this->prepare_money_response( $container->get_regular_price(), $num_decimals );
		$container_price_data['base_price_tax']     = \WC_MNM_Product_Prices::get_tax_ratios( $container );

		$container_price_data['price']         = $container_price_data['base_price'];
		$container_price_data['regular_price'] = $container_price_data['base_regular_price'];
		$container_price_data['price_tax']     = $container_price_data['base_price_tax'];

		$totals = new \stdClass();

		$totals->price          = 0;
		$totals->regular_price  = 0;
		$totals->price_incl_tax = 0;
		$totals->price_excl_tax = 0;

		$container_price_data['base_price_subtotals'] = $totals;
		$container_price_data['base_price_totals']    = $totals;

		$container_price_data['addons_totals'] = $totals;

		$container_price_data['subtotals'] = $totals;
		$container_price_data['totals']    = $totals;

		$child_items = $container->get_child_items();

		if ( empty( $child_items ) ) {
			return [];
		}

		foreach ( $child_items as $child_item ) {

			$child_product    = $child_item->get_product();
			$child_product_id = $child_product->get_id();

			// Skip any product that isn't purchasable.
			if ( ! $child_product->is_purchasable() ) {
				continue;
			}

			$container_price_data['is_sold_individually'][ $child_product_id ] = $child_product->is_sold_individually();
			$container_price_data['product_ids'][ $child_product_id ]          = $child_product_id;
			$container_price_data['prices'][ $child_product_id ]               = $this->prepare_money_response( $child_product->get_price(), $num_decimals );
			$container_price_data['regular_prices'][ $child_product_id ]       = $this->prepare_money_response( $child_product->get_regular_price(), $num_decimals );
			$container_price_data['prices_tax'][ $child_product_id ]           = \WC_MNM_Product_Prices::get_tax_ratios( $child_product );
			$container_price_data['quantities'][ $child_product_id ]           = 0;
			$container_price_data['child_item_subtotals'][ $child_product_id ] = $totals;
			$container_price_data['child_item_totals'][ $child_product_id ]    = $totals;

		}

		return $container_price_data;
	}

	/**
	 * Render interactivity API powered notices that can be added client-side. This re-uses classes
	 * from the woocommerce/store-notices block to ensure style consistency
	 * 
	 * @since 1.0.0
	 *
	 * @param string $block_html The block HTML.
	 * @return string The rendered store notices HTML.
	 */
	private function wrap_status_ui_store_region( $block_html ) {
		$context = array(
			'notices' => array(),
		);

		ob_start();
		?>
		<div
			data-wp-interactive="wc-mix-and-match/add-to-cart/status"
			<?php echo \wp_interactivity_data_wp_context( $context ); ?>
		>
			<?php echo $block_html; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Convert monetary values from WooCommerce to string based integers, using
	 * the smallest unit of a currency
	 * 
	 * @since 1.0.0
	 *
	 * @param string|float  $amount
	 * @param int           $decimals
	 * @param int           $rounding_mode
	 * @return string
	 */
	private function prepare_money_response( $amount, $decimals = 2, $rounding_mode = PHP_ROUND_HALF_UP ) {
		return woocommerce_store_api_get_formatter( 'money' )->format(
			$amount,
			array(
				'decimals'      => $decimals,
				'rounding_mode' => $rounding_mode,
			)
		);
	}
}
