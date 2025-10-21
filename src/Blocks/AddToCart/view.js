/**
 * External dependencies
 */
import { store, getConfig, getContext } from '@wordpress/interactivity';

/**
 * @todo
 * Should we to separate this store from the Add to Cart With Options store?
 * In the Add to Cart with options store we need to override with our own setQuantity and our own addToCart action.
 * Actually i think core's setQuantities is ok...
 * allowsIncrease needs to look at the total of the container count
 * can we get individual quantity popup/toast errors?
 */

/**
 * Internal dependencies
 */
import {
	calcTotalQuantity,
	getTaxedTotals,
	numberRound,
	generateNoticeId,
} from '../utils';

const formatPrice = window.wc.priceFormat.formatPrice; // @todo - This is undefined in no-block themes.

// Stores are locked to prevent 3PD usage until the API is stable.
const universalLock =
	'I acknowledge that using a private store means my plugin will inevitably break on the next store release.';

const { actions, callbacks, state } = store(
	'woocommerce/add-to-cart-with-options',
	{
		state: {
			get config() {
				const { quantity, containerId } = getContext();
				const { [ containerId ]: _, ...config } = quantity;
				return config ?? {};
			},
			get container() {
				const { containerId, containers } = getContext();
				return containers[ containerId ] ?? {};
			},
			get containerPrice() {
				return state?.totals?.price ?? 0;
			},
			get containerQty() {
				const { config } = state;
				return calcTotalQuantity( config || {} );
			},
			get maxContainerSize() {
				const { container } = getContext();
				return container?.max_container_size || '';
			},
			get minContainerSize() {
				const { container } = getContext();
				return container?.min_container_size || 0;
			},
			get isLoading() {
				const { isLoading } = getContext();
				return isLoading ?? false;
			},
			get isPricedPerProduct() {
				return state?.priceData?.per_product_pricing ?? false;
			},
			get isValid() {
				const { validationNotices } = getContext();
				if ( validationNotices && validationNotices.length ) {
					return ! validationNotices.some(
						( notice ) => notice.type === 'error'
					);
				}
				return true;
			},
			get validationNotices() {
				const { validationNotices } = getContext();
				return validationNotices ?? [];
			},
			get priceData() {
				return state?.container?.price_data ?? {};
			},
			get priceSuffix() {
				const { price_display_suffix: suffix } = getConfig();

				// @todo - should we put prices into minor/cents units?
				return suffix
					.replace(
						'{price_including_tax}',
						formatPrice( state.totals.priceInclTax * 100 )
					)
					.replace(
						'{price_excluding_tax}',
						formatPrice( state.totals.priceExclTax * 100 )
					);
			},
			get subTotals() {
				const { subTotals } = getContext();
				if ( subTotals && Object.keys( subTotals ).length ) {
					return subTotals;
				}
				return {};
			},
			get totals() {
				// Set base price totals.
				const basePrice = Number( state?.priceData?.base_price ?? 0 );
				const baseRegularPrice = Number(
					state?.priceData?.base_regular_price ?? 0
				);
				const basePriceTax = state?.priceData?.base_price_tax ?? {
					incl: 0,
					excl: 0,
				};

				// Non-recurring (sub)totals.
				const totals = getTaxedTotals(
					basePrice,
					baseRegularPrice,
					basePriceTax,
					1
				);

				Object.entries( state.config ).forEach( ( [ productId ] ) => {
					const subTotal = state?.subTotals?.[ productId ] || {};

					totals.price += numberRound( subTotal.price ?? 0 );
					totals.regularPrice += numberRound(
						subTotal.regularPrice ?? 0
					);
					totals.priceInclTax += numberRound(
						subTotal.priceInclTax ?? 0
					);
					totals.priceExclTax += numberRound(
						subTotal.priceExclTax ?? 0
					);
				} );
				return totals;
			},
			get validationContext() {
				const { container } = getContext();
				return container?.validation_context || 'add-to-cart';
			},
		},
		actions: {
			addNotice( notice ) {
				const { validationNotices } = state;

				const noticeId = generateNoticeId();

				validationNotices.push( {
					...notice,
					id: noticeId,
				} );

				// Add error to the upstream store.
				if ( notice?.type === 'error' ) {
					actions.addError( {
						code: notice.code || 'genericMixAndMatchError',
						message: notice.message || 'An error occurred.',
						group: 'mix-and-match-product',
					} );
				}

				return noticeId;
			},
			/**
			 * Calculates the subtotals of each child item.
			 */
			calculateSubTotals: () => {
				const context = getContext();
				const { config, priceData } = state;

				const subTotals = {};

				// Loop through config. @todo - should we loop through config? or where are all child items?
				Object.entries( config ).forEach( ( [ productId, qty ] ) => {
					const price = priceData?.prices?.[ productId ] ?? 0.0;
					const regularPrice =
						priceData?.regular_prices?.[ productId ] ?? 0.0;
					const taxRatios = priceData?.prices_tax?.[ productId ] ?? {
						incl: 0,
						excl: 0,
					};

					const totals = getTaxedTotals(
						price,
						regularPrice,
						taxRatios,
						qty
					);

					subTotals[ productId ] = totals;
				} );

				context.subTotals = subTotals;
			},
			clearNotices() {
				const { validationNotices } = state;
				validationNotices.length = 0;

				// Clear upstream errors.
				actions.clearErrors( 'mix-and-match-product' );
			},
			reset: () => {
				const settings = getConfig();

				const context = getContext();

				// eslint-disable-next-line no-alert
				if ( window.confirm( settings.i18n_confirm_reset ) ) {
					context.config = {};
					context.notices = [];
				}
			},
		},
		callbacks: {
			init() {},
			watch() {
				callbacks.validate();

				if ( state.isPricedPerProduct ) {
					// @todo - do we also check is purchasable?
					actions.calculateSubTotals();
				}
			},
			validate: () => {
				const settings = getConfig();

				const { containerId } = getContext();

				// Reset notices each time.
				actions.clearNotices();

				const {
					containerQty,
					maxContainerSize,
					minContainerSize,
					validationContext,
				} = state;

				if ( containerId ) {
					let errorMessage = '';
					let validMessage = '';

					// Validation.
					switch ( true ) {
						// Validate a fixed size container.
						case minContainerSize === maxContainerSize:
							validMessage =
								typeof settings[
									'i18n_' +
										validationContext +
										'_valid_fixed_message'
								] !== 'undefined'
									? settings[
											'i18n_' +
												validationContext +
												'_valid_fixed_message'
									  ]
									: settings.i18n_valid_fixed_message;

							if ( containerQty !== minContainerSize ) {
								errorMessage =
									minContainerSize === 1
										? settings.i18n_qty_error_single
										: settings.i18n_qty_error;
								errorMessage = errorMessage
									.replace( '%s', minContainerSize )
									.replace( '%v', '' )
									.trim();

								actions.addNotice( {
									code: 'invalidContainerSize',
									message: errorMessage,
									type: 'error',
								} );
							}

							break;

						// Validate that a container has fewer than the maximum number of items.
						case maxContainerSize > 0 && minContainerSize === 0:
							validMessage =
								typeof settings[
									'i18n_' +
										validationContext +
										'_valid_max_message'
								] !== 'undefined'
									? settings[
											'i18n_' +
												validationContext +
												'_valid_max_message'
									  ]
									: settings.i18n_valid_max_message;

							if ( containerQty > maxContainerSize ) {
								errorMessage =
									maxContainerSize > 1
										? settings.i18n_max_qty_error
										: settings.i18n_max_qty_error_singular;
								errorMessage = errorMessage
									.replace( '%max', maxContainerSize )
									.replace( '%v', '' )
									.trim();

								actions.addNotice( {
									code: 'invalidContainerSize',
									message: errorMessage,
									type: 'error',
								} );
							}

							break;

						// Validate a range.
						case maxContainerSize > 0 && minContainerSize > 0:
							validMessage =
								typeof settings[
									'i18n_' +
										validationContext +
										'_valid_range_message'
								] !== 'undefined'
									? settings[
											'i18n_' +
												validationContext +
												'_valid_range_message'
									  ]
									: settings.i18n_valid_range_message;

							if (
								containerQty < minContainerSize ||
								containerQty > maxContainerSize
							) {
								errorMessage = settings.i18n_min_max_qty_error;
								errorMessage = errorMessage
									.replace( '%max', maxContainerSize )
									.replace( '%min', minContainerSize )
									.replace( '%v', '' )
									.trim();

								actions.addNotice( {
									code: 'invalidContainerSize',
									message: errorMessage,
									type: 'error',
								} );
							}
							break;

						// Validate that a container has minimum number of items.
						case minContainerSize >= 0:
							validMessage =
								typeof settings[
									'i18n_' +
										validationContext +
										'_valid_min_message'
								] !== 'undefined'
									? settings[
											'i18n_' +
												validationContext +
												'_valid_min_message'
									  ]
									: settings.i18n_valid_min_message;

							if ( containerQty < minContainerSize ) {
								errorMessage =
									minContainerSize > 1
										? settings.i18n_min_qty_error
										: settings.i18n_min_qty_error_singular;
								errorMessage = errorMessage
									.replace( '%min', minContainerSize )
									.replace( '%v', '' )
									.trim(); // @todo - should we replace %v? and remove it from the translations entirely?

								actions.addNotice( {
									code: 'invalidContainerSize',
									message: errorMessage,
									type: 'error',
								} );
							}

							break;
					}

					if ( state.isValid && validMessage !== '' ) {
						validMessage = validMessage
							.replace( '%max', maxContainerSize )
							.replace( '%min', minContainerSize )
							.replace( '%v', '' )
							.trim();

						actions.addNotice( {
							code: 'validContainerSize',
							message: validMessage,
							type: 'success',
						} );
					}
				}
			},
		},
	},
	{
		lock: universalLock,
	}
);
