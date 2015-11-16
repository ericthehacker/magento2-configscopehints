# EW/ConfigScopeHints

This is a Magento 2 port of my [Magento 1 Config Scope Hints module](https://github.com/ericthehacker/magento-configscopehints).

This module shows information in the System Configuration when a field is overridden at more specific scope(s), along with information about these scope(s).

## Installation

This module can be installed manually or by using Composer (recommended).

### Manual Installation

First, download contents of this repo into `app/code/EW/ConfigScopeHints` using a command similar to the following in the Magento 2 root.
```
$ mkdir -p app/code/EW # create vendor directory
$ wget https://github.com/ericthehacker/magento2-configscopehints/archive/master.zip # download zip of module contents
$ unzip master.zip -d app/code/EW # unzip module into vendor directory
$ mv app/code/EW/magento2-configscopehints-master app/code/EW/ConfigScopeHints # correct directory name
$ rm master.zip # clean up zip file
```

Finally, enable module by running the following from the command line at the Magento 2 root.
```
$ php -f bin/magento module:enable EW_ConfigScopeHints 
$ php -f bin/magento setup:upgrade
```

### Composer Installation

Each of these commands should be run from the command line at the Magento 2 root.

First, add this repository to your `composer.json` by running the following.
```
# add this repository to your composer.json
$ composer config repositories.magento2-configscopehints git https://github.com/ericthehacker/magento2-configscopehints.git

# require module
$ composer require ericthehacker/magento2-configscopehints

# enable module
$ php -f bin/magento module:enable EW_ConfigScopeHints 
$ php -f bin/magento setup:upgrade
```

Sit back and enjoy!

## Usage

After installing the module, when viewing a system configuration field, an alert icon will be shown next to the field scope when the field's value is overridden at a more specific level.

The icon is only shown when the value is overridden at a more specific scope than the current one – that is, if viewing at the default scope, overrides at the website or store view level are shown, but if viewing at the website level, only overrides below the currently selected website are shown.

Clicking on the notification bulb displays a detailed list of the exact scope(s) that override the value, with links directly to those scopes.

![Screenshot of system config scope hints module](https://ericwie.se/assets/img/work/magento2-configscopehints.png?v=1)

## Compatibility and Technical Notes

This module was written and tested against version [2.0.0-rc](https://github.com/magento/magento2/releases/tag/2.0.0-rc). The hints are accomplished using intercepters, so there should be no compatibility concerns ([unlike Magento 1](https://github.com/ericthehacker/magento-configscopehints#rewrites)). This version is post-RC, so the intercepters API should stable at this point.
