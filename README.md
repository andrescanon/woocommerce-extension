# STACC's WooCommerce extension
### Connects STACC's recommendation system with the front end of webstores using WordPress + WooCommerce  

[![Build Status](https://travis-ci.com/stacc-dasso/woocommerce-extension.svg?branch=dev)](https://travis-ci.com/stacc-dasso/woocommerce-extension)

### Installing

A step by step tutorial to get the extension up and running.
1. Install WordPress
2. Install WooCommerce
3. Download ZIP of the latest release from [here](https://github.com/stacc-dasso/woocommerce-extension/releases)
4. Upload the ZIP file through WordPress's admin panel plugin install functionality

## Deployment

Currently it isn't possible to connect to the API without the help of the authors of the plugin.
Because of this, most of the functionality can't be seen.

For logging (by default debug loggins is disabled) we use WooCommerce logger, so the logs will available from:  
`Dashboard -> WooCommerce -> Status -> Logs -> StaccDefault.log`

To see if the events are caught you can also enable [wordpress logging](https://codex.wordpress.org/Debugging_in_WordPress),
which makes some basic data visible on:  
`wp-content/debug.log`

## Built With

* [PHP](http://php.net/)
* [Steward](https://github.com/lmc-eu/steward) - For Automated Testing


## Versioning

[SemVer](http://semver.org/) will be used for versioning. 
## Authors

* **Lauri Leiten** - *Team lead, Back end* - [Starrimus](https://github.com/Starrimus)
* **Hannes Saariste** - *Front end, help with back end* - [ilysion](https://github.com/ilysion)
* **Stiivo Siider** - *Back end help, testing* - [StiivoSiider](https://github.com/StiivoSiider)
* **Martin Jürgel** - *Back end help, documentation* - [martin457](https://github.com/martin457)
