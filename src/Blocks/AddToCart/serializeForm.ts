/**
 * Serialize all form inputs into a single object.
 *
 * @param {HTMLFormElement} form The add to cart form to serialize.
 * @return {Object<string, string|Array<string>|Object<string, string>>} Serialized form data.
 */
export const serializeForm = ( form ) => {
	const data = {};
	const formData = new FormData( form );
	formData.forEach( ( value, key ) => {
		// Remove trailing `[]` from keys like "pao-addon-1[]"
		key = key.replace( /\[\]$/, '' );
		// Handle serialized keys like `quantity[10]` and `quantity[22]`
		const match = key.match( /^([^\[]+)\[(\d+)\]$/ );
		if ( match ) {
			const baseKey = match[ 1 ]; // e.g., "quantity"
			const index = match[ 2 ]; // e.g., "10" or "22"
			if ( ! data[ baseKey ] ) {
				data[ baseKey ] = {}; // Initialize as an object
			}
			data[ baseKey ][ index ] = value;
			return;
		}
		// For normal keys, handle duplicate entries
		if ( data[ key ] ) {
			data[ key ] = Array.isArray( data[ key ] )
				? [ ...data[ key ], value ]
				: [ data[ key ], value ];
		} else {
			data[ key ] = value;
		}
	} );
	return data;
};
