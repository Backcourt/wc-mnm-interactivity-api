# Mix and Match Product Interactivity API

## Quickstart

This is a developmental repo. Clone this repo and run `npm install && composer install && npm run build`   
OR    
|[Download latest release](https://github.com/backcourt/wc-same-page-checkout/releases/latest)|
|---|

### What's This?

Experimental plugin that registers the Mix and Match blocks for use in the Add to Cart with Options block.

>**Warning**

1. This is provided _as is_ and does not receive priority support.
2. Please test thoroughly before using in production.
3. Requires WordPress 6.5+
4. Requires WooCommerce 10.3+
5. Requires WooCommerce Mix and Match Products 2.8+
6. Enable admin features in WooCommerce with the following code snippet:
```[php]
add_filter( 'woocommerce_admin_features', function( $features ) {
	$features[] = 'experimental-blocks';
	$features[] = 'blockified-single-product-template-simple';
	$features[] = 'blockified-add-to-cart';
	$features[] = 'blockified-add-to-cart-mix-and-match';
	return $features;
}, 20 );
```
7. Edit the single product block template to include the Add to Cart with Options block.