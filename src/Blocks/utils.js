/**
 * External dependencies
 */
import { getConfig } from '@wordpress/interactivity';

const settings = getConfig( 'woocommerce/add-to-cart-with-options' );

/**
 * Calculate the total quantity from a configuration object.
 *
 * @param {Object<number, number>} config - An object mapping IDs to quantities.
 * @return {number} The sum of all quantities in the config.
 *
 * @example
 * const config = { 98: 1, 99: 2 };
 * const total = calcTotalQuantity(config); // 3
 */
export const calcTotalQuantity = ( config ) => {
	return Object.values( config ).reduce(
		( total, qty ) => total + Number( qty ),
		0
	);
};

/**
 * Generate a semi-random notice ID.
 *
 * @return {string} A unique notice ID.
 */
export const generateNoticeId = () => {
	return `${ Date.now() }-${ Math.random()
		.toString( 36 )
		.substring( 2, 15 ) }`;
};

/**
 * Quantity total message builder.
 *
 * NB: not currently used in the UI, but kept for potential future use.
 *
 * @param {number} qty
 * @return {string} The quantity message.
 */
export const selectQuantityMessage = function ( qty ) {
	const message =
		qty === 1
			? settings.i18n_qty_message_single
			: settings.i18n_qty_message;
	return message.replace( '%s', qty );
};

/**
 * Counter message builder.
 *
 * @param {number} qty
 * @param {number} min
 * @param {number} max
 * @return {string} The counter text.
 */
export const counterText = function ( qty, min, max ) {
	let counter =
		max === 1
			? settings.i18n_quantity_format_counter_single
			: settings.i18n_quantity_format_counter;

	counter = counter.replace( '%s', qty ).replace( '%min', min );

	if ( max !== '' ) {
		counter = counter.replace( '%max', max );
	} else {
		counter = counter.replace( '/%max', '' );
	}

	return settings.i18n_status_format
		.replace( /<[^>]*>/g, '' )
		.replace( '%v', '' )
		.replace( '%s', counter )
		.trim();
};

/**
 * Formats price values according to WC settings.
 *
 * @param {number} number The value to format
 * @param {Object} args
 *
 * @example
 * const args = {
 *   decimal_sep      : string,
 *   currency_position: string,
 *   trim_zeros       : bool,
 *   num_decimals     : int,
 *   return           : "string" | "integer" // NB: frontend components render prices from an integer where $1.00 looks like 100
 * }
 *
 * @return {string} The formatted price.
 */
function formatPrice( number, args ) {
	const defaults = {
		decimal_sep: settings.currency_format_decimal_sep,
		thousands_sep: settings.currency_format_thousand_sep,
		num_decimals: settings.currency_format_num_decimals,
		trim_zeros: settings.currency_format_trim_zeros,
		return: 'string',
	};

	args = Object.assign( defaults, args );

	let n = number;
	const c = isNaN( ( args.num_decimals = Math.abs( args.num_decimals ) ) )
		? 2
		: args.num_decimals;
	const d = args.decimal_sep === undefined ? ',' : args.decimal_sep;
	const t = args.thousands_sep === undefined ? '.' : args.thousands_sep;
	const s = n < 0 ? '-' : '';
	const i = parseInt( ( n = Math.abs( +n || 0 ).toFixed( c ) ), 10 ) + '';
	const j = i.length > 3 ? i.length % 3 : 0;

	let formattedPrice =
		s +
		( j ? i.substring( 0, j ) + t : '' ) +
		i.substring( j ).replace( /(\d{3})(?=\d)/g, '$1' + t ) +
		( c
			? d +
			  Math.abs( n - i )
					.toFixed( c )
					.slice( 2 )
			: '' );

	if ( args.return === 'integer' ) {
		formattedPrice = Math.round(
			parseFloat( formattedPrice ) * Math.pow( 10, parseInt( c, 10 ) )
		).toString();
	} else if ( args.trim_zeros ) {
		const regex = new RegExp( '\\' + args.decimal_sep + '0+$', 'i' );
		formattedPrice = formattedPrice.replace( regex, '' );
	}

	return formattedPrice;
}

/**
 * Converts numbers to formatted price strings. Respects WC price format settings.
 *
 * @param {number} price Price in minor unit, e.g. cents.
 * @param {Object} args
 *
 * @example
 * const args = {
 * 			decimal_sep:       {string}
 *			currency_position: {string}
 *			currency_symbol:   {string}
 *			trim_zeros:        {bool},
 *			num_decimals:      {number},
 *			html:              {bool},
 * }
 *
 * @return {string} Formatted price HTML.
 */
export const formattedPriceHTML = function ( price, args ) {
	const defaults = {
		decimal_sep: settings.currency_format_decimal_sep,
		currency_position: settings.currency_position,
		currency_symbol: settings.currency_symbol,
		trim_zeros: settings.currency_format_trim_zeros,
		num_decimals: settings.currency_format_num_decimals,
		minor_units: true, // Is this price in minor units?
		html: true,
	};

	args = Object.assign( defaults, args );

	price = args.minor_units ? price / 10 ** args.num_decimals : price;

	let formattedPrice = formatPrice( price, args );

	const formattedSymbol = args.html
		? '<span class="woocommerce-Price-currencySymbol">' +
		  args.currency_symbol +
		  '</span>'
		: args.currency_symbol;

	switch ( args.currency_position ) {
		case 'left':
			formattedPrice = formattedSymbol + formattedPrice;
			break;
		case 'right':
			formattedPrice = formattedPrice + formattedSymbol;
			break;
		case 'left_space':
			formattedPrice = formattedSymbol + ' ' + formattedPrice;
			break;
		case 'right_space':
			formattedPrice = formattedPrice + ' ' + formattedSymbol;
			break;
	}

	formattedPrice = args.html
		? '<span class="woocommerce-Price-amount amount"><bdi>' +
		  formattedPrice +
		  '</bdi></span>'
		: formattedPrice;

	return formattedPrice;
};

/**
 * Counter (x/y items) builder.
 *
 * @param {number} qty
 * @return {string} The counter text.
 */
export const counterTextMessage = function ( qty ) {
	const message =
		qty === 1
			? settings.i18n_quantity_format_counter_single
			: settings.i18n_quantity_format_counter;
	return message.replace( '%s', qty );
};

/**
 * Rounds price values according to WC settings.
 *
 * @param {number} number
 * @param {number} precision
 * @return {number} The rounded number.
 */
export const numberRound = function ( number, precision ) {
	precision =
		typeof precision !== 'undefined'
			? parseInt( precision, 10 )
			: settings.currency_format_precision_decimals;
	const factor = Math.pow( 10, precision );
	const tempNumber = number * factor;
	const roundedTempNumber = Math.round( tempNumber );
	return roundedTempNumber / factor;
};

/* eslint-disable-next-line jsdoc/check-line-alignment */
/**
 * Calculate totals by applying tax ratios to raw prices.
 *
 * @param {number} price The base price of the item.
 * @param {number} regularPrice The regular (non-sale) price of the item.
 * @param {Object} taxRatios An object containing tax ratios, e.g. incl and excl properties.
 * @param {number} qty The quantity of items.
 * @return {Object} The calculated totals object, e.g. price, regularPrice, priceInclTax, priceExclTax.
 */
export const getTaxedTotals = function ( price, regularPrice, taxRatios, qty ) {
	qty = qty ?? 1;

	const taxRatioIncl = taxRatios?.incl ? Number( taxRatios.incl ) : false;
	const taxRatioExcl = taxRatios?.excl ? Number( taxRatios.excl ) : false;

	const totals = {
		price: qty * price,
		regularPrice: qty * regularPrice,
		priceInclTax: qty * price,
		priceExclTax: qty * price,
	};

	if ( taxRatioIncl && taxRatioExcl ) {
		totals.priceInclTax = numberRound( totals.price * taxRatioIncl );
		totals.priceExclTax = numberRound( totals.price * taxRatioExcl );

		if ( settings.tax_display_shop === 'incl' ) {
			totals.price = totals.priceInclTax;
			totals.regularPrice = numberRound(
				totals.regularPrice * taxRatioIncl
			);
		} else {
			totals.price = totals.priceExclTax;
			totals.regularPrice = numberRound(
				totals.regularPrice * taxRatioExcl
			);
		}
	}

	return totals;
};
