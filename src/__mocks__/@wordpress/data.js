export const useSelect = ( fn ) =>
	fn( () => ( { getBlockAttributes: () => ( { className: '' } ) } ) );
