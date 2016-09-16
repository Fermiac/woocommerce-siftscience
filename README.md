# woocommerce-siftscience
A plugin that integrates SiftScience fraud detection into your WooCommerce store

WARNING: This code is currently in beta. Testers are needed.

# Setup / Installation from Repo

If you're checking out the latest code from the repository, you'll need node/npm to compile the React App.

Assuming that's all running correctly, you'll need to type the following commands:
- `npm install`
- `webpack`

You should then be able to drop the folder in your WordPress plugins folder and use the plugin

#Development

If you're developing the React App (Order controls on the order page, or metadata handling in settings), 
then you might want to consider running the webpack server for faster reloading of your changes.

To do this:
- Add `define('WP_SIFTSCI_DEV', true);` either at the top of the main plugin file, or in wp-config.php
- In the siftscience plugin folder run: `npm start`

The Webpack server will start up and automatically refresh every time you edit your js code.
