# Change Log for Plugin Version 4.0

* General
    * Updating Hanson Logo and Branding across all plugin option panels and toolbars
    * Core Plugin Performance Improvements
* PHP 7 Updates
    * Converting concatenated string->variables to use template strings
    * Cleaned up sanitation of fields to prevent saving unchanged values
    * Adding help link to core Plugin options page to GitHub
    * Core plugin restructure
        * Splitting plugin Admin options panel logic into /includes
        * Splitting logic that enqueues scripts on Theme front-end into /includes
        * Splitting helpers used across back-end and front-end into /helpers
* Bug Fixes
    * Fixed issue where plugin stylesheet wasn’t being Enqued when adding a new page or post due to change in WP Core but would appear when editing existing pages and posts



# HanBootStrapper-for-WordPress
This plugin works in conjunction with the internal HanBootStrapper JS. This plugin installs hbs.js automatically and allows developers to hook in javascript controllers based on pages, sections and actions.

![HBS Main Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen.png)

## Install

1. Download or Clone Plugin
2. From within WordPress, click "Plugins" -> "Add New" -> "Upload Plugin"
3. Locate Zipped up version of HanBootStrapper-for-WordPress, Upload and Activate

## Settings & Configuration
A menu will appear within the WordPress Dashboard menu labeled "HanBootStrapper" containing options for Namespace, Custom Post Types and User Access.

![HBS Menu Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen-menu.png)

#### General Settings
**Project Namespace** - This should be the 3 letter job-code used for the project. This will need to match the activated themes /assets/js/ controller path. *e.g. /assets/js/HAN.*

**PHP Debugging** - When turned on, PHP will send logs to the browser console notifying whether the current page is missing a data-section or data-page. It will also provide warnings if it fails to locate the page's JS Controller file. If you recieve the "cannot find file" error, it's usually due to the local theme's JS controller folder name not matching the plugin namespace option or the filename not matching the page's data-section value.

![HBS General Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen-general.png)

#### Custom Post Type Settings

This portion of the plugin options allows you to set Data Section and Data Page values globally for default Posts and Custom Post Types. This prevents having to provide JS Controller reference to each and every post you create. Once you provide Data Section and Data Page values and click "Save Changes" the plugin will update all existing post meta. When you create a new post, the Data Section and Data Page meta boxes will pre-populate. You always have the ability to overwrite these at the individual post level but note that if you update Data Section and Data Page within the plugin options, your post data will be updated globally again.

![HBS Posts Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen-cpts.png)

#### User Access

User Access Options allow you to choose which WordPress users are permitted access to use the plugin. If this is the first time visiting the options page, all administrators will be pre-checked. You are required to permit access to at-least one user to prevent being entirely locked out of the plugin.

![HBS Posts Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen-users.png)

#### Theme level requirements

In order for your theme to output the correct data-section and data-page attribute values. You must call the below functions within your theme's body tag. If you're using HanTheme this is not neccessary as it's bundled with the theme.

![HBS Posts Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-screen-dsdp.png)

### Working with JS Controllers

![HBS Posts Screen](https://github.com/hansoninc/HanBootStrapper-for-WordPress/blob/master/images/screens/hbs-project-screen.png)

Bring the HanBootStrapper for Wordpress "theme" folder files into your local project. The controller folder name should match the plugin namespace options. You will also need to update the namespace within main.js and any controllers you clone.