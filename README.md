# Sift.com Fraud Detection for WooCommerce 

A plugin that integrates [Sift](https://sift.com) fraud detection into your WooCommerce store

## Installation

This plugin can be installed directly from the WordPres store.
Setup requires Sift.com to be added to the settings. 

## Installation from Repo

For development/test purposes, you can check this repository out directly to your WordPress plugins folder. 

## VueJS Development

This plugin uses VueJS for the batch-upload component in the settings section
and in the "sift" column that is added to the orders page.
The code can be found in the `/dist` directory.

There is no build/transpiling necessary as the VueJS components are written in plain JS.

## Docker Development

You can use a pre-configured Docker image with WordPress and WooCommerce setup for local testing.
Simply run `docker-compose up` from your command line to start this environment.
You can then navigate to [http://localhost](http://localhost) to try things out.
The username and password are both set to `wordpress`.

## Linting

https://rcorreia.com/wordpress-development/install-php-linter-windows-10-sublime-text-3/

```
docker run -v "$PWD/.:/wcs" -i nabsul/wordpress-phpcs:latest phpcs /wcs --extensions=php
```
