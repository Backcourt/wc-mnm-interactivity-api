<?php
/**
 * Template Functions
 *
 * Functions for the WooCommerce Mix and Match templating system.
 *
 * @package  WooCommerce Mix and Match Products/Functions
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Mix and Match single product template functions.
|--------------------------------------------------------------------------
*/

/**
 * Checks whether this product is a Mix and Match container product and has child items.
 *
 * @since  1.0.0
 *
 * @param  mixed int|WC_Product $product Product ID or WC_Product object.
 * @return boolean
 */
function wc_mnm_container_has_child_items( $product ): bool {

	if ( ! $product instanceof \WC_Product ) {
		$product = wc_get_product( $product );
	}

	if ( ! $product || ! wc_mnm_is_product_container_type( $product ) ) {
		return false;
	}

	if ( ! $product->has_child_items() ) {
		return false;
	}

	return true;
}


/**
 * Checks whether this product is a Mix and Match or Variable Mix and Match type.
 *
 * @since  1.0.0
 *
 * @param  mixed int|WC_Product $product Product ID or WC_Product object.
 * @return boolean
 */
function wc_mnm_is_product_type( $product ): bool {

	if ( ! $product instanceof \WC_Product ) {
		$product = wc_get_product( $product );
	}

	if ( ! $product || ! $product->is_type( array( 'mix-and-match', 'variable-mix-and-match' ) ) ) {
		return false;
	}

	return true;
}