# HanBootStrapper-for-WordPress
This plugin works in conjunction with the internal HanBootStrapper JS. This plugin installs hbs.js automatically and allows developers to hook in javascript controllers based on pages, sections and actions.

![HBS Main Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen.png)

## Install

1. Download or Clone Plugin
2. From within WordPress, click "Plugins" -> "Add New" -> "Upload Plugin"
3. Locate Zipped up version of HanBootStrapper-for-WordPress, Upload and Activate

## Settings & Configuration
A menu will appear within the WordPress Dashboard menu labeled "HanBootStrapper" containing options for Namespace, Custom Post Types and User Access.

![HBS Main Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen-menu.png)


#### General Settings
**Project Namespace** - This should be the 3 letter job-code used for the project. This will need to match the activated themes /assets/js/ controller path. *e.g. /assets/js/HAN.*

**PHP Debugging** - When turned off, PHP will send logs to the browser console notifying whether the current page does not have a data-section or data-page set in addition to failing to locate a JS controller file. If you recieve the "cannot find file" error, it's usually due to the local theme's JS controller folder name not matching the plugin namespace option or the filename not matching the page's data-section value.
