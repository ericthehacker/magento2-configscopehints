# EW/ConfigScopeHints

This is a Magento 2 port of my [Magento 1 Config Scope Hints module](https://github.com/ericthehacker/magento-configscopehints).

This module shows information in the System Configuration when a field is overridden at more specific scope(s), along with information about these scope(s).

## Usage

After installing the module, when viewing a system configuration field, an alert icon will be shown next to the field scope when the field's value is overridden at a more specific level.

The icon is only shown when the value is overridden at a more specific scope than the current one – that is, if viewing at the default scope, overrides at the website or store view level are shown, but if viewing at the website level, only overrides below the currently selected website are shown.

Clicking on the notification bulb displays a detailed list of the exact scope(s) that override the value, with links directly to those scopes.
