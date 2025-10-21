Helga the Viking
  May 5th at 6:10 PM
wc.priceFormat.formatPrice is undefined in non-block themes. what's the workaround?
4 replies


Nadir Seghir, Woo Engineer
:woo:  May 6th at 3:17 AM
enqueue the price format package?
3:17
I don't see how it's related to block themes, maybe a block there is already loading it


Helga the Viking
  May 6th at 10:48 AM
Presumably woo blocks are loading it as a dependency and when NOT using the woo add to cart block it's not there. What is the script's handle?


Nadir Seghir, Woo Engineer
:woo:  May 6th at 10:53 AM
wc-price-format https://github.com/woocommerce/woocommerce/blob/4ce48d6047306f01a9b9fdc86f0a9e6b8421f1be/plugins/woocommerce/src/Blocks/AssetsController.php#L84
GitHubGitHub
woocommerce/plugins/woocommerce/src/Blocks/AssetsController.php at 4ce48d6047306f01a9b9fdc86f0a9e6b8421f1be · woocommerce/woocommerce
A customizable, open-source ecommerce platform built on WordPress. Build any commerce solution you can imagine. - woocommerce/woocommerce (61 kB)
https://github.com/woocommerce/woocommerce/blob/4ce48d6047306f01a9b9fdc86f0a9e6b8421f1be/plugins/woocommerce/src/Blocks/AssetsController.php#L84







