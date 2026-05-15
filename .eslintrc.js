module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	rules: {
		// Project-specific overrides
	},
	overrides: [
		{
			files: [ '**/*.test.js', '**/*.test.jsx', '**/test/**', '**/__mocks__/**' ],
			env: { jest: true },
			rules: {
				'import/no-extraneous-dependencies': 'off',
			},
		},
	],
};
