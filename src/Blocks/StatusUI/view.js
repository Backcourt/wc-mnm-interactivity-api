/**
 * External dependencies
 */
import { getContext, getElement, store } from '@wordpress/interactivity';
import DOMPurify from 'dompurify';

/**
 * Internal dependencies
 */
import './style.scss';
import { ICON_PATHS, ALLOWED_TAGS, ALLOWED_ATTR } from './constants';
import { counterText, formattedPriceHTML } from '../utils';

// Stores are locked to prevent 3PD usage until the API is stable.
const universalLock =
	'I acknowledge that using a private store means my plugin will inevitably break on the next store release.';

// Extend the store.
const { state } = store(
	'woocommerce/add-to-cart-with-options',
	{
		state: {
			get counterText() {
				const { containerQty, minContainerSize, maxContainerSize } =
					state;
				return counterText(
					containerQty,
					minContainerSize,
					maxContainerSize
				);
			},
			get displayStatus() {
				if ( state.counterText ) {
					return 'block';
				}
				return 'none';
			},
			get role() {
				const { notice } = getContext();
				if ( notice?.type === 'error' || notice?.type === 'success' ) {
					return 'alert';
				}
				return 'status';
			},
			get noticeContent() {
				const { notice } = getContext();
				return notice.message.replace( /&hellip;/g, '...' ).trim(); // Replace &hellip; with ... and trim whitespace.
			},
			get iconPath() {
				const { notice } = getContext();
				return ICON_PATHS[ notice?.type ] || ICON_PATHS.notice;
			},
			get isError() {
				const { notice } = getContext();
				return notice?.type === 'error';
			},
			get isSuccess() {
				const { notice } = getContext();
				return notice?.type === 'success';
			},
			get isInfo() {
				const { notice } = getContext();
				return notice?.type === 'notice';
			},
		},
		callbacks: {
			updatePrice() {
				const { ref } = getElement();

				const { containerPrice } = state;

				if ( ref ) {
					const priceHTML = formattedPriceHTML( containerPrice );

					// need to handle suffixes somewhere?

					if ( typeof priceHTML === 'string' ) {
						ref.innerHTML = DOMPurify.sanitize( priceHTML, {
							ALLOWED_TAGS,
							ALLOWED_ATTR,
						} );
					}
				}
			},
			scrollIntoView() {
				const { ref } = getElement();

				if ( ref ) {
					ref.scrollIntoView( { behavior: 'smooth' } );
				}
			},
		},
	},
	{
		lock: universalLock,
	}
);
