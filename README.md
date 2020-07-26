# Sift.com Fraud Detection for WooCommerce 

A plugin that integrates [Sift](https://sift.com) fraud detection into your WooCommerce store

## Installation

This plugin can be installed directly from the WordPres store.
Setup requires Sift.com to be added to the settings. 

## Installation from Repo

For development/test purposes, you can check this repository out directly to your WordPress plugins folder. 

## VueJS Development

This plugin uses VueJS for the batch-upload component in the settings section,
and in the "sift" column that is nadded to the orders page.
The code can be found in the `/app` directory.
Build versions of the VueJS components are in the `/dist` directory. 
To rebuild them you will need to install the VueJS tools with the following command:

```shell script
npm install -g @vue/cli @vue/cli-service-global
```

You can then rebuild the VueJS components as follows:

```shell script
cd app
vue build BatchUpload.vue -t lib
cp dist\BatchUpload.umd.js ../dist
vue build OrderControl.vue -t lib
cp dist\OrderControl.umd.js ../dist
```

## Docker Development

You can use a pre-configured Docker image with WordPress and WooCommerce setup for local testing.
Simply run `docker-compose up` from your command line to start this environment.
You can then navigate to [http://localhost](http://localhost) to try things out.
The username and password are both set to `wordpress`.
