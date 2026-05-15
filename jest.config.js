const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config' );
const path = require( 'path' );

const wpMocks = ( pkg ) =>
	path.resolve( __dirname, `src/__mocks__/@wordpress/${ pkg }.js` );

module.exports = {
	...defaultConfig,
	setupFilesAfterEnv: [
		...( defaultConfig.setupFilesAfterEnv ?? [] ),
		'@testing-library/jest-dom',
	],
	moduleNameMapper: {
		...( defaultConfig.moduleNameMapper ?? {} ),
		'^@wordpress/api-fetch$': wpMocks( 'api-fetch' ),
		'^@wordpress/block-editor$': wpMocks( 'block-editor' ),
		'^@wordpress/components$': wpMocks( 'components' ),
		'^@wordpress/data$': wpMocks( 'data' ),
		'^@wordpress/i18n$': wpMocks( 'i18n' ),
		// @wordpress/element is already installed — use the real thing.
	},
};
