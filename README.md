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
- Add `define('WP_SIFTSCI_DEV', 'http://localhost:8085/app.js');` either at the top of the main plugin file, or in wp-config.php
- In the siftscience plugin folder run: `npm start`

The Webpack server will start up and automatically refresh every time you edit your js code.

#Docker Development

With docker you can get a development environment setup much more quickly:

- Add `127.0.0.1 wcss.test` to your hosts file (/etc/hosts or c:\Windows\System32\Drivers\etc\)
- Build the image with `docker-compose build`
- Start up the image with `docker-compose up`

This will create a basic WordPress installation with nothing installed, so 
you'll still need to install WordPress, WooCommerce, StoreFront theme, etc. to really 
get started.

Once you do, you should be able to activate the WooCommerce-SiftScience plugin and it 
will just start working.
