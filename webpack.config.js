const path = require( 'path' );

const babelSettings = {
	cacheDirectory: true,
	presets: [
		'es2015',
		'stage-1',
		'react'
	],
	plugins: [
		"add-module-exports",
	],
	babelrc: false,
};

module.exports = {
	entry: {
		'app': [ 'babel-polyfill', './app/index.js' ],
	},
	output: {
		path: path.join( __dirname, 'dist' ),
		filename: '[name].js',
		publicPath: 'http://localhost:8085/',
	},
	module: {
		loaders: [
			{
				test: /\.js$/,
				loaders: [
					'babel?' + JSON.stringify( babelSettings ),
					'eslint',
				],
				include: /app/,
				exclude: /node_modules/,
			},
		]
	},
	resolve: {
		alias: {
			'react': path.join( __dirname, 'node_modules', 'react' ),
			'react-dom': path.join( __dirname, 'node_modules', 'react-dom' ),
			'redux': path.join( __dirname, 'node_modules', 'redux' ),
		},
		extensions: [ '', '.json', '.js', '.jsx' ],
		root: [
			path.join( __dirname, 'app' ),
			path.join( __dirname, 'node_modules' ),
		],
	},
};
