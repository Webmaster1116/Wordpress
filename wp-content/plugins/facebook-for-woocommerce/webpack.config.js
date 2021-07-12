// Load the default @wordpress/scripts config object
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

// Legacy jQuery UI powered admin files
const jQueryUIAdminFileNames = [
	'google-product-category-fields',
	'infobanner',
	'metabox',
	'modal',
	'orders',
	'product-categories',
	'product-sets-admin',
	'products-admin',
	'settings-commerce',
	'settings-sync',
];

const jQueryUIAdminFileEntries = {};

jQueryUIAdminFileNames.forEach( ( name ) => {
	jQueryUIAdminFileEntries[ `admin/${ name }` ] = `./assets/js/admin/${ name }.js`;
} );

module.exports = {
	...defaultConfig,
	entry: {
		// Use admin/index.js for any new React-powered UI
		'admin/index': './assets/js/admin/index.js',
		...jQueryUIAdminFileEntries,
	},
	output: {
		filename: '[name].js',
		path: __dirname + '/assets/build',
	},
};
