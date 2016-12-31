# EW_ConfigScopeHints

This module shows information in the Store Configuration backend UI when a config field is overridden at more specific scope(s), along with information about these scope(s).

## Installation

This module can be installed manually or by using Composer (recommended).

### Composer Installation

Each of these commands should be run from the command line at the Magento 2 root.

```bash
# add this repository to your composer.json
$ composer config repositories.magento2-configscopehints git https://github.com/ericthehacker/magento2-configscopehints.git

# require module
$ composer require ericthehacker/magento2-configscopehints

# enable module
$ php -f bin/magento module:enable EW_ConfigScopeHints 
$ php -f bin/magento setup:upgrade
```

### Manual Installation

First, download contents of this repo into `app/code/EW/ConfigScopeHints` using a command similar to the following in the Magento 2 root.

```bash
$ mkdir -p app/code/EW # create vendor directory
$ wget https://github.com/ericthehacker/magento2-configscopehints/archive/master.zip # download zip of module contents
$ unzip master.zip -d app/code/EW # unzip module into vendor directory
$ mv app/code/EW/magento2-configscopehints-master app/code/EW/ConfigScopeHints # correct directory name
$ rm master.zip # clean up zip file
```

Finally, enable module by running the following from the command line at the Magento 2 root.

```bash
$ php -f bin/magento module:enable EW_ConfigScopeHints 
$ php -f bin/magento setup:upgrade
```

Sit back and enjoy!

## Usage

After installing the module, when viewing a system configuration field, an alert icon and message will be shown below the field value if it has been overridden at a more specific scope.

The icon is only shown when the value is overridden at a more specific scope than the current one – that is, if viewing at the default scope, overrides at the website or store view level are shown, but if viewing at the website level, only overrides below the currently selected website are shown.

Along with the alert message, a detailed list of the exact scope(s) that override the value, with links directly to the store config for the current section at those scopes. Clicking an override hint row arrow will expand the row to also show the field's value at that scope.

![Screenshot of system config scope hints module](https://ericisaweso.me/images/magento2-configscopehints-v3.1.png)

## Compatibility and Technical Notes

Version 3.0.0 of this module has been tested against Magento 2.1.x. It's likely compatible with 2.0.x as well, but this is untested.

> NOTE: For known compatibility with 2.0.x, check out version [2.1.0][2.1.0] of the module.

## Known Issues

### MAGETWO-62648

When used on Magento 2.1.3, the module can produce a false positive when viewing a website scope. If a given config value has been overridden at this website scope, any children store views which have "Use Website" set for the value will incorrectly show as being overridden. 

This is a known [core bug][MAGETWO-62648].


[2.1.0]: https://github.com/ericthehacker/magento2-configscopehints/releases/tag/v2.1.0
[MAGETWO-62648]: https://github.com/magento/magento2/issues/7943