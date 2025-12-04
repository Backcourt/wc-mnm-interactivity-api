<?php
/**
 * Mix and Match Add to Cart template part
 *
 * @package Backcourt\MixAndMatch\iAPI\Templates
 * 
 * @since   1.0.0
 * @version 1.0.0
 */

?>
<!-- wp:wc-mix-and-match/add-to-cart -->

	<!-- wp:heading {"textAlign":"left","align":"full"} -->
	<h2 class="wp-block-heading alignfull has-text-align-left"><?php echo wp_kses_post( 'Choose <span data-wp-text="state.minContainerSize">[wc-mnm/minContainerSize]</span> selections', '[Frontend]', 'woocommerce-mix-and-match-products' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:wc-mix-and-match/add-to-cart-child-items {"displayLayout":{"type":"flex","columns":3,"shrinkColumns":true}} -->

		<!-- wp:wc-mix-and-match/add-to-cart-child-item-template -->

			<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"1rem"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
			<div
				class="wp-block-group"
				style="margin-bottom: 1rem"
			>
				
				<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail"} -->
					<!-- wp:woocommerce/product-sale-badge {"align":"right"} /-->
				<!-- /wp:woocommerce/product-image -->

				<!-- wp:group {"layout":{"type":"constrained"}} -->
				<div class="wp-block-group">

					<!-- wp:post-title {"level":3,"isLink":false,"textAlign":"center","style":{"spacing":{"margin":{"top":"0","right":"0","bottom":"0rem"}},"typography":{"fontStyle":"normal","fontWeight":"400"}},"fontSize":"medium","__woocommerceNamespace":"woocommerce/product-query/product-title"} /-->

					<!-- wp:woocommerce/product-rating {"textAlign":"center"} /-->

					<!-- wp:woocommerce/product-price {"textAlign":"center","fontSize":"small"} /-->

					<!-- wp:wc-mix-and-match/add-to-cart-product-price {"textAlign":"center","fontSize":"small"} /-->

					<!-- wp:woocommerce/product-stock-indicator {"textAlign":"center","fontSize":"small"} /-->
				</div>
				<!-- /wp:group -->

				<!-- wp:wc-mix-and-match/add-to-cart-child-item-quantity-selector {"textAlign":"center","quantitySelectorStyle":"stepper"} /-->

			</div>
			<!-- /wp:group -->

		<!-- /wp:wc-mix-and-match/add-to-cart-child-item-template -->

		<!-- wp:wc-mix-and-match-no-child-items -->
			<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center","flexWrap":"wrap"}} -->
			<div class="wp-block-group">
				<!-- wp:paragraph {"fontSize":"medium"} -->
				<p class="has-medium-font-size"><?php echo esc_html_x( 'This product is currently unavailable.', '[Frontend]', 'wc-mnm-interactivity-api' ); ?></p>
				<!-- /wp:paragraph -->

			</div>
			<!-- /wp:group -->
		<!-- /wp:wc-mix-and-match-no-child-items -->

	<!-- /wp:wc-mix-and-match/add-to-cart-child-items -->

	<!-- wp:wc-mix-and-match/add-to-cart-reset-button {"style":{"spacing":{"margin":{"bottom":"1rem"}}} /-->

	<!-- wp:wc-mix-and-match/add-to-cart-status-ui {"style":{"spacing":{"margin":{"bottom":"1rem"}}} /-->

<!-- /wp:wc-mix-and-match/add-to-cart -->

