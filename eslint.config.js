/**
 * ESLint flat config (ESLint 10, `@wordpress/scripts` 32+). Replaces the
 * legacy .eslintrc.js, which flat config no longer reads.
 */
/* eslint-disable import/no-extraneous-dependencies -- transitive dep of @wordpress/scripts */
const wpPlugin = require( '@wordpress/eslint-plugin' );
/* eslint-enable import/no-extraneous-dependencies */
const jestPlugin = require( 'eslint-plugin-jest' );

module.exports = [
	{
		ignores: [ 'build/**', 'vendor/**', 'node_modules/**', '.claude/**' ],
	},
	{
		// ESLint flat config discovers only *.js, *.cjs, *.mjs on its own.
		// Name every other extension here or the rules below never see it.
		files: [ '**/*.jsx', '**/*.ts', '**/*.tsx' ],
	},
	...wpPlugin.configs.recommended,
	// The unit suite stays on Jest (wp-scripts test-unit-jest); the default
	// test override in @wordpress/eslint-plugin 27+ targets Vitest instead.
	{
		...jestPlugin.configs[ 'flat/recommended' ],
		files: [
			'**/*.test.js',
			'**/*.test.jsx',
			'**/test/**',
			'**/__mocks__/**',
		],
	},
	{
		files: [
			'**/*.test.js',
			'**/*.test.jsx',
			'**/test/**',
			'**/__mocks__/**',
		],
		rules: {
			'import/no-extraneous-dependencies': 'off',
		},
	},
];
