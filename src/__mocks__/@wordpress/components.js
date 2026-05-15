// Strip WP-specific boolean props that React would warn about on DOM elements.
const WP_PROPS = new Set( [
	'__next40pxDefaultSize',
	'__nextHasNoMarginBottom',
	'__nextUnconstrainedSize',
] );
const clean = ( props ) =>
	Object.fromEntries(
		Object.entries( props ).filter( ( [ k ] ) => ! WP_PROPS.has( k ) )
	);

const React = require( 'react' );
const el = React.createElement;

export const PanelBody = ( { children, title } ) =>
	el( 'div', { title }, children );
export const TextControl = ( { label, value, onChange, ...rest } ) =>
	el( 'input', {
		'aria-label': label,
		value: value ?? '',
		onChange: ( e ) => onChange?.( e.target.value ),
		...clean( rest ),
	} );
export const Button = ( { children, onClick, disabled } ) =>
	el( 'button', { onClick, disabled }, children );
export const Spinner = () => el( 'span', { 'aria-label': 'Loading' } );
export const SelectControl = ( {
	label,
	value,
	options = [],
	onChange,
	...rest
} ) =>
	el(
		'select',
		{
			'aria-label': label,
			value: value ?? '',
			onChange: ( e ) => onChange?.( e.target.value ),
			...clean( rest ),
		},
		options.map( ( o ) =>
			el( 'option', { key: o.value, value: o.value }, o.label )
		)
	);
export const __experimentalUnitControl = ( {
	label,
	value,
	onChange,
	...rest
} ) =>
	el( 'input', {
		'aria-label': label,
		value: value ?? '',
		onChange: ( e ) => onChange?.( e.target.value ),
		...clean( rest ),
	} );
