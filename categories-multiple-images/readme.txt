=== Categories Multiple Images ===
Contributors: binternet
Tags: category, taxonomy, images
Requires at least: 4.1
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Categories Multiple Images Plugin allow you to add image unlimited images to category or any other taxonomy.

== Description ==

This plugin is an extended version of [Categories Images](https://wordpress.org/plugins/categories-images/).

The Categories Multiple Images Plugin allow you to add unlimited amount of image to a category or any other taxonomy.

You can use: 
<?php Categories_Multiple_Images::get_image( term_id, image_number, image_size, use_placeholder ); ?> 

to get the direct image url and put it in any <img> tag in your template.

Also from settings menu you can exclude any taxonomies from the plugin to avoid conflicting with another plugins like WooCommerce!

More documentation
https://github.com/Binternet/WordPress-Plugin-Categories-Multiple-Images

== Installation ==

e.g.

1. Put the plugin directory in your plugins directory (Usually `/wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php Categories_Multiple_Images::get_image( term_id, image_number, image_size, use_placeholder ); ?> ` in your templates

== Changelog ==

= 1.0 =
* First release, my first plugin ;-)

