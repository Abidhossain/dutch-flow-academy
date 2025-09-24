const path = require( 'path' ),
	defaultConfig = require( '@wordpress/scripts/config/webpack.config' ),
	WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

module.exports = {
	...defaultConfig,
	devtool    : 'source-map',
	entry      : {
		// frontend.
		'yith-wcdp': './assets/js/src/yith-wcdp.js',

		// backend.
		'admin/yith-wcdp': './assets/js/admin/src/yith-wcdp.js',

		// blocks.
		'blocks/grand-total': './assets/js/blocks/src/grand-total/index.js',
		'blocks/grand-total-frontend': './assets/js/blocks/src/grand-total/frontend.js',
	},
	mode: 'production',
	module: {
		rules: [
			{
				test: /\.(js|jsx)$/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env', '@babel/react' ],
						plugins: [ [ '@babel/transform-runtime' ] ],
					},
				}
			}
		]
	},
	optimization: {
		minimize: false,
	},
	output     : {
		filename: (pathData, assetInfo) => {
			let name = pathData.chunk.name,
				components = name.split( '/' ),
				fileName = components?.[components.length - 1];

			if ( ! fileName.startsWith( 'yith-wcdp' ) ) {
				fileName = `yith-wcdp-${fileName}`;
			}

			components[components.length - 1] = `${fileName}.bundle.js`;

			return components.join( '/' );
		},
		path: path.resolve( __dirname, 'assets/js' ),
		libraryTarget: 'window'
	},
	resolve: {
		extensions: ['*', '.js', '.jsx'],
	},
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) =>
				! [
					'DependencyExtractionWebpackPlugin',
					'CleanWebpackPlugin',
				].includes( plugin.constructor.name )
		),
		new WooCommerceDependencyExtractionWebpackPlugin(),
	],
};
