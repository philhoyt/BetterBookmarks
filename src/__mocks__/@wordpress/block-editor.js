export const useBlockProps = ( props = {} ) => ( {
	...props,
	'data-testid': 'block-root',
} );
export const InspectorControls = ( { children } ) => children;
export const useSettings = () => [ [] ];
