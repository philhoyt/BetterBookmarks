/**
 * Jest configuration.
 *
 * `@wordpress/scripts` 36 moved test-unit-js to Vitest and stopped shipping
 * Jest defaults. This keeps the existing Jest suite on the published
 * WordPress preset, per the scripts package's vitest-migration guide
 * ("Keep an existing Jest suite").
 */
const path = require( 'path' );
const preset = require( '@wordpress/jest-preset-default/jest-preset' );

const wpMocks = ( pkg ) =>
	path.resolve( __dirname, `src/__mocks__/@wordpress/${ pkg }.js` );

module.exports = {
	preset: '@wordpress/jest-preset-default',
	transform: {
		'\\.[jt]sx?$': [
			'babel-jest',
			{ presets: [ '@wordpress/babel-preset-default' ] },
		],
	},
	setupFilesAfterEnv: [
		...preset.setupFilesAfterEnv,
		'@testing-library/jest-dom',
	],
	moduleNameMapper: {
		...preset.moduleNameMapper,
		'^@wordpress/api-fetch$': wpMocks( 'api-fetch' ),
		'^@wordpress/block-editor$': wpMocks( 'block-editor' ),
		'^@wordpress/components$': wpMocks( 'components' ),
		'^@wordpress/data$': wpMocks( 'data' ),
		'^@wordpress/i18n$': wpMocks( 'i18n' ),
		// @wordpress/element is already installed — use the real thing.
	},
};
