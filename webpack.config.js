/**
 * Custom Webpack configuration for Studiolsm Utils.
 *
 * - Discovers SCSS files inside src/modules and compiles them to build/modules/<module>/assets/css.
 * - Copies the remaining PHP and asset files from src to build.
 */

const fs = require('fs');
const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const CopyWebpackPlugin = require('copy-webpack-plugin');

class RemoveEmptyJsAssetsPlugin {
	apply(compiler) {
		compiler.hooks.compilation.tap('RemoveEmptyJsAssetsPlugin', (compilation) => {
			const stage = compiler.webpack.Compilation.PROCESS_ASSETS_STAGE_SUMMARIZE;

			compilation.hooks.processAssets.tap(
				{
					name: 'RemoveEmptyJsAssetsPlugin',
					stage,
				},
				() => {
					for (const asset of compilation.getAssets()) {
						if (!asset.name.endsWith('.js')) {
							continue;
						}

						const assetSource = asset.source.source().toString().trim();

						if (assetSource.length === 0) {
							compilation.deleteAsset(asset.name);
						}
					}
				}
			);
		});
	}
}

const rootDir = process.cwd();
const srcDir = path.resolve(rootDir, 'src');

/**
 * Recursively collect SCSS files inside a directory.
 *
 * @param {string} directory Base directory.
 * @returns {string[]} Array of absolute file paths.
 */
const collectScssFiles = (directory) => {
	if (!fs.existsSync(directory)) {
		return [];
	}

	const entries = [];

	const walk = (current) => {
		for (const entry of fs.readdirSync(current, { withFileTypes: true })) {
			const fullPath = path.join(current, entry.name);

			if (entry.isDirectory()) {
				walk(fullPath);
				continue;
			}

			if (entry.isFile() && entry.name.endsWith('.scss')) {
				entries.push(fullPath);
			}
		}
	};

	walk(directory);

	return entries;
};

const scssFiles = collectScssFiles(path.resolve(srcDir, 'modules'));
const entries = {};

scssFiles.forEach((filePath) => {
	const relativeFromSrc = path.relative(srcDir, filePath).split(path.sep).join('/');
	const outputName = relativeFromSrc
		.replace('/scss/', '/css/')
		.replace(/\.scss$/, '');

	entries[outputName] = filePath;
});

const config = {
	...defaultConfig,
};

config.entry = Object.keys(entries).length > 0 ? entries : {};

config.output = {
	...config.output,
	path: path.resolve(rootDir, 'build'),
	clean: true,
};

config.plugins = [
	...(config.plugins || []),
	new RemoveEmptyJsAssetsPlugin(),
	new CopyWebpackPlugin({
		patterns: [
			{
				from: path.resolve(srcDir),
				to: path.resolve(rootDir, 'build'),
				noErrorOnMissing: true,
			},
		],
	}),
];

module.exports = config;
