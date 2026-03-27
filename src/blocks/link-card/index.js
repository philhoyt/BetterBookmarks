import { registerBlockType } from '@wordpress/blocks';
import { SVG, Path } from '@wordpress/primitives';
import metadata from './block.json';
import Edit from './edit.jsx';
import './style.css';

const icon = (
	<SVG viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
		<Path d="M17 2H7a2 2 0 0 0-2 2v17a1 1 0 0 0 1.5.87L12 19.14l5.5 2.73A1 1 0 0 0 19 21V4a2 2 0 0 0-2-2zm0 17.27-4.5-2.23a1 1 0 0 0-.9 0L7 19.27V4h10v15.27z" />
	</SVG>
);

registerBlockType( metadata.name, {
	icon,
	edit: Edit,
	save: () => null,
} );
